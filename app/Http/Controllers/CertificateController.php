<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Registration;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\Admin\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use ZipArchive;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    /**
     * One certificate template per belt, most specific first.
     * "Brown Belt 4th Kyu" must match before a generic "brown" template.
     */
    public const BELT_TEMPLATE_NAMES = [
        'White Belt',
        'Yellow Belt',
        'Orange Belt',
        'Green Belt',
        'Blue Belt',
        'Purple Belt',
        'Brown Belt 4th Kyu',
        'Brown Belt 3rd Kyu',
        'Brown Belt 2nd Kyu',
        'Brown Belt 1st Kyu',
        'Black Shodan',
    ];

    private const BELT_TEMPLATE_KEYS = [
        'brown 4th kyu',
        'brown 3rd kyu',
        'brown 2nd kyu',
        'brown 1st kyu',
        'black shodan',
        'white',
        'yellow',
        'orange',
        'green',
        'blue',
        'purple',
        'brown',
    ];

    /** Cached belt templates for current request (id => CertificateTemplate) */
    private $beltTemplateCache = null;
    /**
     * Display a listing of certificate events
     */
    public function index()
    {
        $certificates = Certificate::with('template')->orderBy('created_at', 'desc')->get();
        return view('admin.certificates.index', compact('certificates'));
    }

    /**
     * Show the form for creating a new certificate event
     */
    public function create()
    {
        $templates = CertificateTemplate::orderBy('name')->get();
        $events = Event::orderByDesc('event_date')->orderByDesc('id')->get(['id', 'title', 'venue', 'event_date']);
        return view('admin.certificates.create', compact('templates', 'events'));
    }

    /**
     * Store a newly created certificate event
     */
    public function store(Request $request)
    {
        $certificateType = $request->input('certificate_type', 'belt');
        $rules = [
            'event_id' => 'required|exists:event,id',
            'event_title' => 'required|string|max:255',
            'event_date' => 'required|date',
            'certificate_type' => 'required|in:belt,competition',
            'excel_file' => 'required|file|mimes:xlsx,csv|max:10240', // 10MB max
        ];
        if ($certificateType === 'competition') {
            $rules['template_id'] = 'required|exists:tbl_certificate_templates,id';
        } else {
            $rules['template_id'] = 'nullable|exists:tbl_certificate_templates,id';
        }
        $request->validate($rules);

        try {
            // Upload Excel file
            $excelFile = $request->file('excel_file');
            $excelPath = $excelFile->store('certificates/excels', 'public');
            
            $venue = $this->venueFromEvent((int) $request->event_id);

            // Parse Excel file (returns participants + skip report)
            $parseResult = $this->parseExcelFile($excelFile, $certificateType, $venue);
            $participants = $parseResult['participants'] ?? [];
            $skipped = $parseResult['skipped'] ?? [];
            
            if (empty($participants)) {
                Storage::disk('public')->delete($excelPath);
                $skipMsg = !empty($skipped)
                    ? ' (' . count($skipped) . ' rows skipped — see details after fixing the file)'
                    : '';
                return redirect()->back()
                    ->with('error', 'No valid participants found in the Excel file.' . $skipMsg)
                    ->with('cert_import_skipped', $skipped);
            }

            // Create certificate record (template_id optional for belt; used only for competition)
            $certificate = Certificate::create([
                'event_id' => $request->event_id,
                'event_title' => $request->event_title,
                'event_date' => $request->event_date,
                'venue' => $venue,
                'certificate_type' => $request->certificate_type,
                'template_id' => $certificateType === 'competition' ? $request->template_id : null,
                'excel_file_path' => $excelPath,
                'participants_data' => $participants,
                'certificate_count' => count($participants),
            ]);

            $imported = count($participants);
            $skippedCount = count($skipped);
            $successMsg = "Certificate event created successfully with {$imported} participants.";
            if ($skippedCount > 0) {
                $reasons = collect($skipped)->pluck('reason')->countBy()->map(fn ($c, $r) => "{$c}× {$r}")->values()->implode('; ');
                $successMsg .= " {$skippedCount} row(s) skipped: {$reasons}.";
            }

            return redirect()->route('admin.certificates.show', $certificate->id)
                ->with('success', $successMsg)
                ->with('cert_import_stats', [
                    'imported' => $imported,
                    'skipped' => $skippedCount,
                ])
                ->with('cert_import_skipped', $skipped);
        } catch (\Exception $e) {
            if (isset($excelPath)) {
                Storage::disk('public')->delete($excelPath);
            }
            return redirect()->back()->with('error', 'Error creating certificate: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified certificate event
     */
    public function show($id)
    {
        $certificate = Certificate::with('template')->findOrFail($id);
        $participants = $certificate->participants_data ?? [];
        return view('admin.certificates.show', compact('certificate', 'participants'));
    }

    /**
     * Legacy method: Show certificate for individual registration (old functionality)
     */
    public function showRegistrationCertificate($registrationId)
    {
$registration = Registration::with(['event.category'])->findOrFail($registrationId);
    $category = strtolower(trim($registration->event->category ?? 'default'));
    return view('admin.certificates.layout', compact('registration', 'category'));
}

    /**
     * Show the form for editing a certificate event
     */
    public function edit($id)
    {
        $certificate = Certificate::with('template')->findOrFail($id);
        $templates = CertificateTemplate::orderBy('name')->get();
        $events = Event::orderByDesc('event_date')->orderByDesc('id')->get(['id', 'title', 'venue', 'event_date']);
        return view('admin.certificates.edit', compact('certificate', 'templates', 'events'));
    }

    /**
     * Update a certificate event
     */
    public function update(Request $request, $id)
    {
        $certificate = Certificate::findOrFail($id);
        $certificateType = $request->input('certificate_type', 'competition');
        $rules = [
            'event_id' => 'required|exists:event,id',
            'event_title' => 'required|string|max:255',
            'event_date' => 'required|date',
            'certificate_type' => 'required|in:belt,competition',
        ];
        if ($certificateType === 'competition') {
            $rules['template_id'] = 'required|exists:tbl_certificate_templates,id';
        } else {
            $rules['template_id'] = 'nullable|exists:tbl_certificate_templates,id';
        }
        $request->validate($rules);

        try {
            $venue = $this->venueFromEvent((int) $request->event_id);
            $participants = $this->applyVenueToParticipants($certificate->participants_data ?? [], $venue);

            $certificate->update([
                'event_id' => $request->event_id,
                'event_title' => $request->event_title,
                'event_date' => $request->event_date,
                'venue' => $venue,
                'certificate_type' => $request->certificate_type,
                'template_id' => $certificateType === 'competition' ? $request->template_id : null,
                'participants_data' => $participants,
            ]);

            return redirect()->route('admin.certificates.index')
                ->with('success', 'Certificate event updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update certificate event: ' . $e->getMessage());
        }
    }

    /**
     * View individual certificate PDF
     */
    public function viewCertificate($certificateId, $registrationId)
    {
        $certificate = Certificate::with('template')->findOrFail($certificateId);
        $participants = $certificate->participants_data ?? [];
        
        $participant = collect($participants)->firstWhere('registration_id', $registrationId);
        
        if (!$participant) {
            abort(404, 'Participant not found');
        }

        $participant = $this->resolveParticipantBelts($participant);
        $template = $this->getTemplateForParticipant($certificate, $participant);
        if (!$template && strtolower($certificate->certificate_type ?? '') === 'belt') {
            abort(404, 'Certificate template not found for this participant\'s belt.');
        }
        $backgroundImage = $this->getBackgroundImageForTemplate($template);

        $certificateType = strtolower($certificate->certificate_type ?? 'belt');
        // Exact 72 DPI PDF points: 1 cm = 28.3464567 pt
        // Belt Certificate (24cm x 33cm): 680.315 pt x 935.433 pt
        // Competition Certificate (21cm x 29.7cm / A4): 595.276 pt x 841.89 pt
        $paperSize = $certificateType === 'competition'
            ? [0, 0, 595.276, 841.89]
            : [0, 0, 680.315, 935.433];

        $pdf = Pdf::loadView('admin.certificates.certificate_pdf', [
            'certificate' => $certificate,
            'participant' => $participant,
            'backgroundImage' => $backgroundImage,
            'certType' => $certificateType,
        ])->setPaper($paperSize, 'portrait')
          ->setOption('enable-html5-parser', true)
          ->setOption('enable-local-file-access', true);

        $filename = Str::slug($participant['name'] ?? 'certificate') . '.pdf';
        return $pdf->stream($filename);
    }

    /**
     * Download all certificates as a single combined PDF (each certificate on a separate page).
     * If ?format=zip is explicitly provided, downloads as ZIP archive.
     */
    public function downloadAll($id)
    {
        $certificate = Certificate::with('template')->findOrFail($id);
        $participants = $certificate->participants_data ?? [];
        
        if (empty($participants)) {
            return redirect()->back()->with('error', 'No participants found for this certificate event.');
        }

        $certificateType = strtolower($certificate->certificate_type ?? 'belt');
        $paperSize = $certificateType === 'competition'
            ? [0, 0, 595.276, 841.89]
            : [0, 0, 680.315, 935.433];

        $built = $this->buildCertificateItems($certificate, $participants, $certificateType);
        $items = $built['items'];
        $skipped = $built['skipped'];
        $total = count($participants);
        $generated = count($items);

        if (empty($items)) {
            $skipHint = !empty($skipped)
                ? ' Skipped: ' . collect($skipped)->pluck('reason')->unique()->implode('; ') . '.'
                : '';
            return redirect()->back()
                ->with('error', 'No valid participants or matching belt templates found.' . $skipHint)
                ->with('cert_download_skipped', $skipped);
        }

        $reportMsg = "Generated {$generated} of {$total} certificates"
            . ($skipped ? ' (' . count($skipped) . ' skipped)' : '') . '.';
        session()->flash('success', $reportMsg);
        if (!empty($skipped)) {
            session()->flash('cert_download_skipped', $skipped);
            session()->flash('warning', count($skipped) . ' participant(s) were skipped during download. See list below.');
        }

        // If explicitly requested as zip archive
        if (request()->query('format') === 'zip') {
            $zipFileName = 'certificates-' . $certificate->id . '-' . time() . '.zip';
            $zipPath = storage_path('app/temp/' . $zipFileName);
            
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
                return redirect()->back()->with('error', 'Could not create ZIP file.');
            }

            foreach ($items as $item) {
                $pdf = Pdf::loadView('admin.certificates.certificate_pdf', [
                    'certificate' => $item['certificate'],
                    'participant' => $item['participant'],
                    'backgroundImage' => $item['backgroundImage'],
                    'certType' => $certificateType,
                ])->setPaper($paperSize, 'portrait')
                  ->setOption('enable-html5-parser', true)
                  ->setOption('enable-local-file-access', true);

                $pdfContent = $pdf->output();
                $safeFilename = $this->createSafeFilename($item['participant']['name'] ?? 'participant') . '.pdf';
                $zip->addFromString($safeFilename, $pdfContent);
            }

            $zip->close();
            return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
        }

        $pdf = Pdf::loadView('admin.certificates.certificates_bulk_pdf', [
            'items' => $items,
            'certType' => $certificateType,
        ])->setPaper($paperSize, 'portrait')
          ->setOption('enable-html5-parser', true)
          ->setOption('enable-local-file-access', true);

        $safeTitle = Str::slug($certificate->event_title ?: 'certificates') . '_combined.pdf';
        return $pdf->download($safeTitle);
    }

    /**
     * Build PDF items for participants; apply next→current belt fallback; collect skips.
     */
    private function buildCertificateItems(Certificate $certificate, array $participants, string $certificateType): array
    {
        $items = [];
        $skipped = [];

        foreach ($participants as $participant) {
            $resolved = $this->resolveParticipantBelts($participant);
            $template = $this->getTemplateForParticipant($certificate, $resolved);

            if (!$template && $certificateType === 'belt') {
                Log::warning('Certificate generation: skipping participant - no template for belt', [
                    'name' => $resolved['name'] ?? null,
                    'next_belt' => $resolved['next_belt'] ?? null,
                    'current_belt' => $resolved['current_belt'] ?? null,
                ]);
                $skipped[] = [
                    'row' => $resolved['registration_id'] ?? null,
                    'name' => $resolved['name'] ?? 'unknown',
                    'reason' => 'No matching belt template for "'
                        . ($resolved['next_belt'] ?: $resolved['current_belt'] ?: 'unknown') . '"',
                ];
                continue;
            }

            $items[] = [
                'certificate' => $certificate,
                'participant' => $resolved,
                'backgroundImage' => $this->getBackgroundImageForTemplate($template),
            ];
        }

        return ['items' => $items, 'skipped' => $skipped];
    }

    /**
     * Parse Excel file and extract participant data.
     * Returns ['participants' => [...], 'skipped' => [['row','name','reason'], ...]].
     */
    private function parseExcelFile($file, $certificateType = 'competition', ?string $venue = null): array
    {
        $participants = [];
        $skipped = [];
        
        try {
            $data = Excel::toArray([], $file);
            
            if (empty($data) || empty($data[0])) {
                return ['participants' => [], 'skipped' => []];
            }

            $rows = $data[0];
            
            // Find header row and map column indices
            $headerRowIndex = -1;
            $columnMap = [];
            
            // Look for header row (usually first few rows)
            for ($i = 0; $i < min(5, count($rows)); $i++) {
                $row = $rows[$i];
                $rowLower = array_map(function($cell) {
                    return strtolower(trim($cell ?? ''));
                }, $row);
                
                // Check if this looks like a header row
                $hasRegistration = false;
                $hasName = false;
                
                foreach ($rowLower as $cell) {
                    if (str_contains($cell, 'registration') && str_contains($cell, 'id')) {
                        $hasRegistration = true;
                    }
                    if (str_contains($cell, 'name') && !str_contains($cell, 'school')) {
                        $hasName = true;
                    }
                }
                
                if ($hasRegistration && $hasName) {
                    $headerRowIndex = $i;
                    // Map columns by header name
                    foreach ($row as $colIndex => $header) {
                        $headerLower = strtolower(trim($header ?? ''));
                        
                        if (str_contains($headerLower, 'registration') && str_contains($headerLower, 'id')) {
                            $columnMap['registration_id'] = $colIndex;
                        } elseif (str_contains($headerLower, 'name') && !str_contains($headerLower, 'school')) {
                            $columnMap['name'] = $colIndex;
                        } elseif (str_contains($headerLower, 'place') || str_contains($headerLower, '場所')) {
                            $columnMap['place'] = $colIndex;
                        } elseif (str_contains($headerLower, 'class')) {
                            $columnMap['class'] = $colIndex;
                        } elseif (str_contains($headerLower, 'school')) {
                            $columnMap['school'] = $colIndex;
                        } elseif (str_contains($headerLower, 'submitted') || str_contains($headerLower, 'date of reg') || str_contains($headerLower, '登録日')) {
                            $columnMap['date_of_reg'] = $colIndex;
                        } elseif (str_contains($headerLower, 'rank')) {
                            $columnMap['rank'] = $colIndex;
                        } elseif (str_contains($headerLower, 'category') || str_contains($headerLower, 'participation')) {
                            $columnMap['category'] = $colIndex;
                        } elseif (str_contains($headerLower, 'current') && str_contains($headerLower, 'belt')) {
                            $columnMap['current_belt'] = $colIndex;
                        } elseif (str_contains($headerLower, 'next') && str_contains($headerLower, 'belt')) {
                            $columnMap['next_belt'] = $colIndex;
                        }
                    }
                    break;
                }
            }
            
            // If no header found, use default column positions
            if ($headerRowIndex === -1) {
                $columnMap = [
                    'registration_id' => 0,
                    'name' => 1,
                    'class' => 2,
                    'school' => 3,
                    'category' => 4, // Participation column
                    'date_of_reg' => 8,
                    'place' => null, // Will try to find or use school
                ];
                $headerRowIndex = 0;
            }
            
            // Extract data rows (skip header row)
            for ($rowIndex = $headerRowIndex + 1; $rowIndex < count($rows); $rowIndex++) {
                $row = $rows[$rowIndex];
                $excelRow = $rowIndex + 1;
                
                // Skip empty rows
                if (empty(array_filter($row, fn ($c) => trim((string)($c ?? '')) !== ''))) {
                    continue;
                }

                // Extract data using column map
                $registrationId = isset($columnMap['registration_id']) && isset($row[$columnMap['registration_id']]) 
                    ? trim((string)$row[$columnMap['registration_id']]) : '';
                $name = isset($columnMap['name']) && isset($row[$columnMap['name']]) 
                    ? trim((string)$row[$columnMap['name']]) : '';
                $class = isset($columnMap['class']) && isset($row[$columnMap['class']]) 
                    ? trim((string)$row[$columnMap['class']]) : '';
                $school = isset($columnMap['school']) && isset($row[$columnMap['school']]) 
                    ? trim((string)$row[$columnMap['school']]) : '';
                $submittedAt = isset($columnMap['date_of_reg']) && isset($row[$columnMap['date_of_reg']]) 
                    ? trim((string)$row[$columnMap['date_of_reg']]) : '';
                $rank = isset($columnMap['rank']) && isset($row[$columnMap['rank']]) 
                    ? trim((string)$row[$columnMap['rank']]) : '';
                $category = isset($columnMap['category']) && isset($row[$columnMap['category']]) 
                    ? trim((string)$row[$columnMap['category']]) : '';
                $currentBelt = isset($columnMap['current_belt']) && isset($row[$columnMap['current_belt']]) 
                    ? trim((string)$row[$columnMap['current_belt']]) : '';
                $nextBelt = isset($columnMap['next_belt']) && isset($row[$columnMap['next_belt']]) 
                    ? trim((string)$row[$columnMap['next_belt']]) : '';

                // Treat placeholders as empty
                if ($this->isBlankBeltValue($currentBelt)) {
                    $currentBelt = '';
                }
                if ($this->isBlankBeltValue($nextBelt)) {
                    $nextBelt = '';
                }

                // Belt event: fall back to current belt instead of skipping when next_belt empty
                if (strtolower($certificateType ?? '') === 'belt') {
                    if ($nextBelt === '' && $currentBelt !== '') {
                        $nextBelt = $currentBelt;
                        Log::warning('Certificate Excel: next_belt empty — falling back to current_belt', [
                            'row_index' => $excelRow,
                            'name' => $name ?: 'unknown',
                            'current_belt' => $currentBelt,
                        ]);
                    } elseif ($nextBelt === '' && $currentBelt === '') {
                        Log::warning('Certificate Excel: skipping row - no next_belt or current_belt', [
                            'row_index' => $excelRow,
                            'name' => $name ?: 'unknown',
                        ]);
                        $skipped[] = [
                            'row' => $excelRow,
                            'name' => $name ?: 'unknown',
                            'reason' => 'Missing Next Belt and Current Belt',
                        ];
                        continue;
                    }
                }

                // Get Place - prioritize Place column, fallback to School
                $place = '';
                if (isset($columnMap['place']) && $columnMap['place'] !== null && isset($row[$columnMap['place']])) {
                    $place = trim((string)$row[$columnMap['place']]);
                } elseif (!empty($school)) {
                    $place = trim($school);
                }
                
                // Also check other columns for Place if not found
                if (empty($place)) {
                    // Check columns 10-15 for Place
                    for ($col = 10; $col <= 15; $col++) {
                        if (isset($row[$col]) && !empty(trim((string)$row[$col]))) {
                            $cellValue = strtolower(trim((string)$row[$col]));
                            // Skip if it looks like a date, number, or other non-place value
                            if (!preg_match('/^\d+$/', $cellValue) && 
                                !preg_match('/\d{4}-\d{2}-\d{2}/', $cellValue) &&
                                !preg_match('/\d{1,2}\/\d{1,2}\/\d{4}/', $cellValue) &&
                                strlen($cellValue) > 2) {
                                $place = trim((string)$row[$col]);
                                break;
                            }
                        }
                    }
                }

                // Skip summary / footer rows from exports
                if (str_contains(strtolower($registrationId), 'total')
                    || str_contains(strtolower($name), 'total attended')
                    || str_contains(strtolower($name), 'belt-wise')) {
                    continue;
                }

                // Validate registration ID
                if ($registrationId === '') {
                    // Likely a footer/breakdown row — skip quietly unless a name was present
                    if ($name !== '') {
                        $skipped[] = [
                            'row' => $excelRow,
                            'name' => $name,
                            'reason' => 'Missing Registration ID',
                        ];
                    }
                    continue;
                }

                // Skip header rows mistaken as data (e.g. "Registration ID", "Name", "School", "Submitted At")
                if ($this->isHeaderRow($registrationId, $name, $place, $submittedAt)) {
                    Log::info('Certificate Excel: skipping header row', [
                        'row_index' => $excelRow,
                        'registration_id' => $registrationId,
                    ]);
                    continue;
                }

                // Unicode-safe name: require non-empty trimmed name (min 1 character)
                if (mb_strlen($name) < 1) {
                    $skipped[] = [
                        'row' => $excelRow,
                        'name' => $registrationId,
                        'reason' => 'Missing participant name',
                    ];
                    continue;
                }

                // Clean date
                $dateOfReg = '';
                if (!empty($submittedAt)) {
                    $dateOfReg = trim($submittedAt);
                    $dateOfReg = preg_replace('/\s+/', ' ', $dateOfReg);
                }

                if ($venue !== null && trim($venue) !== '') {
                    $place = trim($venue);
                }

                $participants[] = [
                    'registration_id' => $registrationId,
                    'name' => $name,
                    'place' => $place,
                    'date_of_reg' => $dateOfReg,
                    'class' => $class,
                    'school' => $school,
                    'rank' => $rank,
                    'category' => $category,
                    'current_belt' => $currentBelt,
                    'next_belt' => $nextBelt,
                ];
            }

            // Only keep participants whose registration_id exists in database (registration_code)
            $filterResult = $this->filterParticipantsByValidRegistration($participants);
            $participants = $filterResult['participants'];
            $skipped = array_merge($skipped, $filterResult['skipped']);
        } catch (\Exception $e) {
            throw new \Exception('Error parsing Excel file: ' . $e->getMessage());
        }

        return ['participants' => $participants, 'skipped' => $skipped];
    }

    /**
     * Venue text stored on the event, unchanged except for surrounding whitespace.
     */
    private function venueFromEvent(int $eventId): string
    {
        $event = Event::findOrFail($eventId);

        return trim((string) $event->venue);
    }

    /**
     * Write the same venue onto every participant so Excel place/school cannot override it.
     */
    private function applyVenueToParticipants(array $participants, string $venue): array
    {
        foreach ($participants as &$participant) {
            $participant['place'] = $venue;
        }
        unset($participant);

        return $participants;
    }

    /**
     * True when belt cell is empty or a placeholder like N/A.
     */
    private function isBlankBeltValue($value): bool
    {
        $v = strtolower(trim((string)$value));
        return $v === '' || in_array($v, ['n/a', 'na', '-', 'none', 'null', '—'], true);
    }

    /**
     * Normalize participant belts: blank/N/A next_belt falls back to current_belt.
     */
    private function resolveParticipantBelts(array $participant): array
    {
        $current = $participant['current_belt'] ?? '';
        $next = $participant['next_belt'] ?? '';
        if ($this->isBlankBeltValue($current)) {
            $current = '';
        }
        if ($this->isBlankBeltValue($next)) {
            $next = '';
        }
        if ($next === '' && $current !== '') {
            $next = $current;
        }
        $participant['current_belt'] = $current;
        $participant['next_belt'] = $next;
        return $participant;
    }

    /**
     * Create safe filename from participant name
     */
    private function createSafeFilename($name)
    {
        $filename = Str::slug($name);
        $filename = preg_replace('/[^a-z0-9-]/', '', $filename);
        $filename = substr($filename, 0, 50); // Limit length
        return $filename ?: 'certificate';
    }

    /**
     * Get cached map of belt key => CertificateTemplate (belt-type templates only). Single query per request.
     */
    private function getBeltTemplateCache()
    {
        if ($this->beltTemplateCache !== null) {
            return $this->beltTemplateCache;
        }
        $templates = CertificateTemplate::where('certificate_type', 'belt')->get();
        $map = [];
        foreach ($templates as $template) {
            $key = $this->normalizeBeltKey($template->name);
            if ($key !== '' && !isset($map[$key])) {
                $map[$key] = $template;
            }
        }
        $this->beltTemplateCache = $map;
        return $this->beltTemplateCache;
    }

    private function beltTemplateAlreadyUsed(string $beltName, ?int $ignoreId = null): bool
    {
        $key = $this->normalizeBeltKey($beltName);
        $templates = CertificateTemplate::where('certificate_type', 'belt')
            ->when($ignoreId, function ($query) use ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            })
            ->get();

        foreach ($templates as $template) {
            if ($this->normalizeBeltKey($template->name) === $key) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalize a belt or template name so each belt in the grading order
     * maps to its own template.
     */
    private function normalizeBeltKey($beltName)
    {
        if ($beltName === null || $beltName === '') {
            return '';
        }
        $normalized = strtolower(trim($beltName));
        $normalized = preg_replace('/\bbelt\b/', ' ', $normalized);
        $normalized = trim(preg_replace('/\s+/', ' ', $normalized));
        foreach (self::BELT_TEMPLATE_KEYS as $key) {
            if ($key === $normalized || str_contains($normalized, $key)) {
                return $key;
            }
        }
        return $normalized ?: '';
    }

    /**
     * Resolve template for one participant. Belt event: use next_belt → template mapping. Competition: use certificate's template.
     * Falls back to current_belt when next_belt is blank / N/A.
     */
    private function getTemplateForParticipant(Certificate $certificate, array $participant)
    {
        $certificateType = strtolower($certificate->certificate_type ?? 'belt');
        if ($certificateType === 'competition') {
            return $certificate->template;
        }

        $participant = $this->resolveParticipantBelts($participant);
        $beltName = $participant['next_belt'] ?: ($participant['current_belt'] ?? '');
        if ($beltName === '') {
            return null;
        }
        $key = $this->normalizeBeltKey($beltName);
        if ($key === '') {
            Log::warning('Certificate generation: no template key for belt', [
                'belt_name' => $beltName,
                'name' => $participant['name'] ?? null,
            ]);
            return null;
        }
        $cache = $this->getBeltTemplateCache();
        $template = $cache[$key] ?? null;
        if (!$template) {
            Log::warning('Certificate generation: template not found for belt', [
                'belt_key' => $key,
                'belt_name' => $beltName,
                'name' => $participant['name'] ?? null,
            ]);
        }
        return $template;
    }

    /**
     * Get base64 data URL for template background image, or null.
     */
    private function getBackgroundImageForTemplate($template)
    {
        if (!$template || !$template->background_image) {
            return null;
        }
        $imagePath = storage_path('app/public/' . $template->background_image);
        if (!file_exists($imagePath)) {
            $imagePath = public_path('storage/' . $template->background_image);
        }
        if (!file_exists($imagePath)) {
            return null;
        }
        $imageData = file_get_contents($imagePath);
        $base64 = base64_encode($imageData);
        $mimeType = mime_content_type($imagePath);
        return 'data:' . $mimeType . ';base64,' . $base64;
    }

    /**
     * Detect if row is a header row mistaken as data (e.g. "Registration ID", "Name", "School", "Submitted At").
     */
    private function isHeaderRow($registrationId, $name, $place, $submittedAt)
    {
        $headerValues = [
            'registration id', 'reg id', 'name', 'school', 'place', 'submitted at',
            'date of reg', '登録日', 'class', 'category', 'rank', 'current belt', 'next belt',
        ];
        $regLower = strtolower(trim($registrationId));
        $nameLower = strtolower(trim($name));
        $placeLower = strtolower(trim($place));
        $dateLower = strtolower(trim($submittedAt));
        foreach ($headerValues as $h) {
            if ($regLower === $h || $nameLower === $h || $placeLower === $h || $dateLower === $h) {
                return true;
            }
        }
        return false;
    }

    /**
     * Filter participants to only those whose registration_id exists in tbl_registration.registration_code.
     * Returns ['participants' => [...], 'skipped' => [...]].
     */
    private function filterParticipantsByValidRegistration(array $participants): array
    {
        if (empty($participants)) {
            return ['participants' => [], 'skipped' => []];
        }
        $registrationIds = array_unique(array_column($participants, 'registration_id'));
        $validCodes = Registration::whereIn('registration_code', $registrationIds)
            ->pluck('registration_code')
            ->flip();
        $filtered = [];
        $skipped = [];
        foreach ($participants as $p) {
            if ($validCodes->has($p['registration_id'])) {
                $filtered[] = $p;
            } else {
                Log::warning('Certificate Excel: skipping row - registration_id not found in database', [
                    'registration_id' => $p['registration_id'],
                    'name' => $p['name'] ?? null,
                ]);
                $skipped[] = [
                    'row' => $p['registration_id'] ?? null,
                    'name' => $p['name'] ?? 'unknown',
                    'reason' => 'Registration ID not found in database',
                ];
            }
        }
        return ['participants' => $filtered, 'skipped' => $skipped];
    }

    /**
     * Template Management Methods
     */
    public function templatesIndex()
    {
        $templates = CertificateTemplate::orderBy('certificate_type')->orderBy('name')->get();
        return view('admin.certificates.templates.index', compact('templates'));
    }

    public function templatesCreate()
    {
        return view('admin.certificates.templates.create', [
            'beltNames' => self::BELT_TEMPLATE_NAMES,
            'selectedBelt' => old('belt_name'),
        ]);
    }

    public function templatesStore(Request $request)
    {
        $request->validate([
            'name' => 'required_unless:certificate_type,belt|nullable|string|max:255',
            'belt_name' => 'required_if:certificate_type,belt|nullable|in:'.implode(',', self::BELT_TEMPLATE_NAMES),
            'description' => 'nullable|string',
            'certificate_type' => 'required|in:belt,competition',
            'background_image' => 'required|image|mimes:jpeg,jpg|max:5120', // 5MB max, required
        ]);

        if ($request->certificate_type === 'belt' && $this->beltTemplateAlreadyUsed($request->belt_name)) {
            return redirect()->back()->withInput()->withErrors([
                'belt_name' => 'A template for '.$request->belt_name.' already exists.',
            ]);
        }

        try {
            $backgroundImagePath = null;
            
            if ($request->hasFile('background_image')) {
                Storage::disk('public')->makeDirectory('certificates/templates');
                $image = $request->file('background_image');
                $backgroundImagePath = $image->store('certificates/templates', 'public');
                if (!$backgroundImagePath) {
                    throw new \RuntimeException('Failed to save template image to storage folder.');
                }
                if (!Storage::disk('public')->exists($backgroundImagePath)) {
                    throw new \RuntimeException('Template image was not found after saving. Check storage permissions.');
                }
            }

            CertificateTemplate::create([
                'name' => $request->certificate_type === 'belt' ? $request->belt_name : $request->name,
                'description' => $request->description,
                'certificate_type' => $request->certificate_type,
                'background_image' => $backgroundImagePath,
            ]);

            return redirect()->route('admin.certificates.templates.index')
                ->with('success', 'Template created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error creating template: ' . $e->getMessage());
        }
    }

    public function templatesEdit($id)
    {
        $template = CertificateTemplate::findOrFail($id);
        $selectedBelt = old('belt_name');
        if ($selectedBelt === null && $template->certificate_type === 'belt') {
            $currentKey = $this->normalizeBeltKey($template->name);
            foreach (self::BELT_TEMPLATE_NAMES as $beltName) {
                if ($this->normalizeBeltKey($beltName) === $currentKey) {
                    $selectedBelt = $beltName;
                    break;
                }
            }
        }

        return view('admin.certificates.templates.edit', [
            'template' => $template,
            'beltNames' => self::BELT_TEMPLATE_NAMES,
            'selectedBelt' => $selectedBelt,
        ]);
    }

    public function templatesUpdate(Request $request, $id)
    {
        $request->validate([
            'name' => 'required_unless:certificate_type,belt|nullable|string|max:255',
            'belt_name' => 'required_if:certificate_type,belt|nullable|in:'.implode(',', self::BELT_TEMPLATE_NAMES),
            'description' => 'nullable|string',
            'certificate_type' => 'required|in:belt,competition',
            'background_image' => 'nullable|image|mimes:jpeg,jpg|max:5120',
        ]);

        if ($request->certificate_type === 'belt' && $this->beltTemplateAlreadyUsed($request->belt_name, (int) $id)) {
            return redirect()->back()->withInput()->withErrors([
                'belt_name' => 'A template for '.$request->belt_name.' already exists.',
            ]);
        }

        try {
            $template = CertificateTemplate::findOrFail($id);
            
            if ($request->hasFile('background_image')) {
                Storage::disk('public')->makeDirectory('certificates/templates');
                $image = $request->file('background_image');
                $backgroundImagePath = $image->store('certificates/templates', 'public');
                if (!$backgroundImagePath) {
                    throw new \RuntimeException('Failed to save template image to storage folder.');
                }
                if (!Storage::disk('public')->exists($backgroundImagePath)) {
                    throw new \RuntimeException('Template image was not found after saving. Check storage permissions.');
                }
                if ($template->background_image) {
                    Storage::disk('public')->delete($template->background_image);
                }
                $template->background_image = $backgroundImagePath;
            }

            $template->update([
                'name' => $request->certificate_type === 'belt' ? $request->belt_name : $request->name,
                'description' => $request->description,
                'certificate_type' => $request->certificate_type,
            ]);

            return redirect()->route('admin.certificates.templates.index')
                ->with('success', 'Template updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating template: ' . $e->getMessage());
        }
    }

    public function templatesDestroy($id)
    {
        try {
            $template = CertificateTemplate::findOrFail($id);
            
            // Check if template is in use
            if ($template->certificates()->count() > 0) {
                return redirect()->back()->with('error', 'Cannot delete template that is in use by certificate events.');
            }

            // Delete background image
            if ($template->background_image) {
                Storage::disk('public')->delete($template->background_image);
            }

            $template->delete();

            return redirect()->route('admin.certificates.templates.index')
                ->with('success', 'Template deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting template: ' . $e->getMessage());
        }
    }
}
