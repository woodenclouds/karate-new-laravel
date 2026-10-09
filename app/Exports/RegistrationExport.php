<?php

namespace App\Exports;

use App\Models\Admin\Event;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Razorpay\Api\Api;
use App\Support\Ist;
use Carbon\Carbon;

class RegistrationExport implements FromCollection, WithEvents, WithStyles, WithCustomStartCell
{
    use Exportable;

    protected Event $event;
    protected array $records = [];
    protected float $totalAmount = 0;

    private array $classGroups = [
        'LKG-UKG'        => ['LKG', 'UKG'],
        'I - II STD'     => ['I STD', 'II STD', 'I – II STD'],
        'III - IV STD'   => ['III STD', 'IV STD', 'III – IV STD'],
        'V - VI STD'     => ['V STD', 'VI STD', 'V – VI STD'],
        'VII - VIII STD' => ['VII STD', 'VIII STD', 'VII – VIII STD'],
        'IX - X STD'     => ['IX STD', 'X STD', 'IX – X STD'],
        'Above'          => ['Above', 'above'],
    ];

    public function __construct(Event $event)
    {
        $this->event = $event->load([
            'category',
            'registrations' => fn($q) => $q->where('status', 'paid')->orderBy('id', 'asc'),
            'eventBeltFees',
        ]);
    }

    public function collection()
    {
        $category = strtolower($this->event->category->name ?? '');

        // Razorpay API keys with config fallback
        $razorpayKey = config('services.razorpay.key') ?? env('RAZORPAY_KEY');
        $razorpaySecret = config('services.razorpay.secret') ?? env('RAZORPAY_SECRET');
        $api = null;
        if (!empty($razorpayKey) && !empty($razorpaySecret)) {
            try {
                $api = new Api($razorpayKey, $razorpaySecret);
            } catch (\Exception $e) {
                $api = null;
            }
        }

        $this->records = $this->event->registrations->map(function ($reg) use ($category, $api) {
            $getVal = function ($keyword) use ($reg) {
                $field = collect($reg->submitted_data)->first(
                    fn($item) => str_contains(strtolower($item['label'] ?? ''), strtolower($keyword))
                );
                return is_array($field['value'] ?? '')
                    ? implode(', ', $field['value'])
                    : ($field['value'] ?? '');
            };

            $beltId      = $getVal('belt');
            $belt        = null;
            if (!empty($beltId)) {
                try {
                    $belt = DB::table('tbl_belt')->where('id', $beltId)->first();
                } catch (\Throwable $e) {
                    $belt = null;
                }
            }
            // Prefer real belt names; leave blank (not "N/A") so certificate import can fall back
            $currentBelt = trim((string)($belt?->from_belt ?? ''));

            // Check if next belt is explicitly in submitted form data
            $nextBeltId = '';
            $nextBeltField = collect($reg->submitted_data)->first(function ($item) {
                if (!isset($item['label'])) {
                    return false;
                }
                $label = strtolower(trim($item['label']));
                $type = strtolower(trim($item['type'] ?? ''));
                if ($label === 'next belt') {
                    return true;
                }
                if ($type === 'belt') {
                    return str_contains($label, 'next') && !str_contains($label, 'current');
                }
                return false;
            });

            if ($nextBeltField && isset($nextBeltField['value'])) {
                $nextBeltId = is_array($nextBeltField['value'])
                    ? implode(', ', $nextBeltField['value'])
                    : (string)($nextBeltField['value'] ?? '');
                $nextBeltId = trim($nextBeltId);
            }

            $nextBelt = '';
            if (!empty($nextBeltId) && is_numeric($nextBeltId)) {
                try {
                    $nextBelt = trim((string)(DB::table('tbl_belt')->where('id', $nextBeltId)->value('from_belt') ?? ''));
                } catch (\Throwable $e) {
                    $nextBelt = '';
                }
            }
            if ($nextBelt === '') {
                $nextBelt = trim((string)($belt?->to_belt ?? ''));
            }
            if ($nextBelt === '') {
                $nextBelt = $currentBelt;
            }

            $class       = $getVal('class');
            $weight      = $getVal('weight');
            $teamType    = $getVal('Participation Type');

            // Map to clean grouped class
            $mappedClass = 'N/A';
            if (!empty($class)) {
                foreach ($this->classGroups as $groupName => $values) {
                    if (in_array($class, $values, true)) {
                        $mappedClass = $groupName;
                        break;
                    }
                }
                if ($mappedClass === 'N/A') {
                    $mappedClass = $class;
                }
            }

            // Determine Amount Paid
            // 1. First check if amount is saved in the database
            $amountPaid = (float)($reg->amount ?? 0);

            // 2. If amount is not set and payment_id exists, try Razorpay API
            if ($amountPaid <= 0 && !empty($reg->payment_id) && $api !== null) {
                try {
                    $payment = $api->payment->fetch($reg->payment_id);
                    $amountPaid = (float)($payment->amount / 100);
                } catch (\Exception $e) {
                    // Razorpay API failed (e.g. >180 days limit) - will fall back to belt/event fee
                    $amountPaid = 0;
                }
            }

            // 3. Fallback to belt fee or event fee if amount is still 0
            if ($amountPaid <= 0) {
                if ($beltId) {
                    try {
                        $eventBeltFee = DB::table('event_belt_fees')
                            ->where('event_id', $this->event->id)
                            ->where('belt_id', $beltId)
                            ->first();
                        if ($eventBeltFee && $eventBeltFee->fee > 0) {
                            $amountPaid = (float)$eventBeltFee->fee;
                        } elseif ($belt && $belt->fees > 0) {
                            $amountPaid = (float)$belt->fees;
                        }
                    } catch (\Throwable $e) {
                        // ignore and fall back to event fee
                    }
                }
                if ($amountPaid <= 0 && !empty($this->event->fee)) {
                    $amountPaid = (float)$this->event->fee + (float)($this->event->additional_fee ?? 0);
                }
            }

            $this->totalAmount += $amountPaid;

            return [
                'Registration ID' => $reg->registration_code,
                'Name'            => $getVal('name') ?: $getVal('student name'),
                'Class'           => $mappedClass,
                'School'          => $getVal('school') ?: $getVal('school name'),
                'Current Belt'    => $currentBelt,
                'Next Belt'       => $nextBelt,
                'Weight'          => $weight,
                'Team/Individual' => $teamType,
                'Amount Paid'     => $amountPaid,
                'Entered By'      => ucfirst($reg->entered_by ?? 'user'),
                'Submitted At'    => Ist::format($reg->created_at),
            ];
        })->toArray();

        // Sort records by class order (LKG to higher), then weight, then registration ID
        $classGroupOrder = array_keys($this->classGroups);
        usort($this->records, function ($a, $b) use ($classGroupOrder) {
            $classA = $a['Class'];
            $classB = $b['Class'];

            $posA = array_search($classA, $classGroupOrder);
            $posB = array_search($classB, $classGroupOrder);

            if ($posA === false) $posA = 999;
            if ($posB === false) $posB = 999;

            if ($posA !== $posB) {
                return $posA <=> $posB;
            }

            $weightA = (float)($a['Weight'] ?? 0);
            $weightB = (float)($b['Weight'] ?? 0);

            if ($weightA != $weightB) {
                return $weightA <=> $weightB;
            }

            return strcmp($a['Registration ID'], $b['Registration ID']);
        });

        return new Collection([]); // empty because we manually write rows in registerEvents
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function registerEvents(): array
    {
        return [
            \Maatwebsite\Excel\Events\AfterSheet::class => function ($event) {
                $sheet = $event->sheet->getDelegate();
                $rowIndex = 1;

                $isCompetition = str_contains(strtolower($this->event->category->name ?? ''), 'competition');

                // Define headers
                $headers = [
                    '#',
                    'Registration ID',
                    'Name',
                    'Class',
                    'School',
                    'Current Belt',
                    'Next Belt',
                ];

                if ($isCompetition) {
                    $headers[] = 'Weight';
                    $headers[] = 'Team/Individual';
                }

                $headers[] = 'Amount Paid';
                $headers[] = 'Entered By';
                $headers[] = 'Submitted At';

                $totalCols = count($headers);
                $lastColLetter = chr(64 + $totalCols);

                // Row 1: Event Title
                $sheet->mergeCells("A{$rowIndex}:{$lastColLetter}{$rowIndex}");
                $sheet->setCellValue("A{$rowIndex}", strtoupper($this->event->title));
                $sheet->getStyle("A{$rowIndex}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 16],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $rowIndex++;

                // Row 2: Event details (Category, Date, Venue)
                $eventDate = $this->event->event_date ?? $this->event->date;
                $dateFormatted = !empty($eventDate) ? Carbon::parse($eventDate)->format('d M Y') : 'N/A';
                $categoryName = $this->event->category->name ?? 'N/A';
                $venue = $this->event->venue ?? 'N/A';

                $sheet->mergeCells("A{$rowIndex}:{$lastColLetter}{$rowIndex}");
                $sheet->setCellValue("A{$rowIndex}", "Category: {$categoryName} | Date: {$dateFormatted} | Venue: {$venue}");
                $sheet->getStyle("A{$rowIndex}")->applyFromArray([
                    'font'      => ['italic' => true, 'size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $rowIndex += 2;

                $amountColLetter = '';
                foreach ($this->recordsByClass() as $className => $rows) {
                    $sheet->mergeCells("A{$rowIndex}:{$lastColLetter}{$rowIndex}");
                    $sheet->setCellValue("A{$rowIndex}", $className.' ('.count($rows).')');
                    $sheet->getStyle("A{$rowIndex}:{$lastColLetter}{$rowIndex}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E7D32']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                    ]);
                    $sheet->getRowDimension($rowIndex)->setRowHeight(22);
                    $rowIndex++;

                    $col = 'A';
                    foreach ($headers as $head) {
                        $sheet->setCellValue($col.$rowIndex, $head);
                        $col++;
                    }
                    $sheet->getStyle("A{$rowIndex}:{$lastColLetter}{$rowIndex}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1B5E20']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                    $sheet->getRowDimension($rowIndex)->setRowHeight(24);
                    $rowIndex++;

                    $startDataRow = $rowIndex;
                    foreach ($rows as $index => $rec) {
                        $col = 'A';
                        foreach ($headers as $key) {
                            if ($key === '#') {
                                $sheet->setCellValue($col.$rowIndex, $index + 1);
                                $sheet->getStyle($col.$rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                            } elseif ($key === 'Amount Paid') {
                                $amountColLetter = $col;
                                $sheet->setCellValue($col.$rowIndex, $rec['Amount Paid'] ?? 0);
                                $sheet->getStyle($col.$rowIndex)->getNumberFormat()->setFormatCode('₹#,##0.00');
                            } else {
                                $sheet->setCellValue($col.$rowIndex, $rec[$key] ?? '');
                            }
                            $col++;
                        }
                        $rowIndex++;
                    }

                    $endDataRow = $rowIndex - 1;
                    if ($endDataRow >= $startDataRow) {
                        $sheet->getStyle("A{$startDataRow}:{$lastColLetter}{$endDataRow}")->applyFromArray([
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['rgb' => 'D0D0D0'],
                                ],
                            ],
                        ]);
                    }

                    $rowIndex++;
                }

                // Totals Row
                $summaryRow = $rowIndex;
                // Find column letter right before Amount Paid
                $prevColIndex = ord($amountColLetter) - 65;
                $beforeAmountCol = chr(64 + $prevColIndex);

                $sheet->mergeCells("A{$summaryRow}:{$beforeAmountCol}{$summaryRow}");
                $sheet->setCellValue("A{$summaryRow}", "Total Registrations: " . count($this->records));
                $sheet->setCellValue("{$amountColLetter}{$summaryRow}", $this->totalAmount);
                $sheet->getStyle("{$amountColLetter}{$summaryRow}")->getNumberFormat()->setFormatCode('₹#,##0.00');

                $sheet->getStyle("A{$summaryRow}:{$lastColLetter}{$summaryRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F5E9']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                    'borders' => [
                        'top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1B5E20']],
                        'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['rgb' => '1B5E20']],
                    ],
                ]);
                $sheet->getRowDimension($summaryRow)->setRowHeight(22);
                $rowIndex += 2;

                // Belt-wise Breakdown Table
                $beltSummary = [];
                foreach ($this->records as $r) {
                    $bName = !empty($r['Next Belt'])
                        ? $r['Next Belt']
                        : (!empty($r['Current Belt']) ? $r['Current Belt'] : 'Other');
                    $beltSummary[$bName] = ($beltSummary[$bName] ?? 0) + 1;
                }

                if (!empty($beltSummary)) {
                    $sheet->mergeCells("A{$rowIndex}:D{$rowIndex}");
                    $sheet->setCellValue("A{$rowIndex}", "BELT-WISE PARTICIPANT BREAKDOWN");
                    $sheet->getStyle("A{$rowIndex}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '1B5E20']],
                    ]);
                    $rowIndex++;

                    $sheet->setCellValue("A{$rowIndex}", "Belt");
                    $sheet->mergeCells("A{$rowIndex}:C{$rowIndex}");
                    $sheet->setCellValue("D{$rowIndex}", "Participant Count");
                    $sheet->getStyle("A{$rowIndex}:D{$rowIndex}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1B5E20']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                    $rowIndex++;

                    $breakdownStart = $rowIndex;
                    foreach ($beltSummary as $bName => $bCount) {
                        $sheet->setCellValue("A{$rowIndex}", $bName);
                        $sheet->mergeCells("A{$rowIndex}:C{$rowIndex}");
                        $sheet->setCellValue("D{$rowIndex}", $bCount);
                        $sheet->getStyle("D{$rowIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $rowIndex++;
                    }
                    $breakdownEnd = $rowIndex - 1;

                    $sheet->getStyle("A{$breakdownStart}:D{$breakdownEnd}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'D0D0D0'],
                            ],
                        ],
                    ]);
                }

                // Auto-size all columns
                foreach (range('A', $lastColLetter) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            }
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }

    /**
     * One list per class group, LKG–UKG first, then higher classes.
     * A class with no students is left out. Unlisted classes come last.
     */
    private function recordsByClass(): array
    {
        $groups = [];
        foreach (array_keys($this->classGroups) as $name) {
            $groups[$name] = [];
        }

        $extra = [];
        foreach ($this->records as $record) {
            $class = $record['Class'] ?: 'N/A';
            if (array_key_exists($class, $groups)) {
                $groups[$class][] = $record;
            } else {
                $extra[$class][] = $record;
            }
        }

        foreach ($extra as $class => $rows) {
            $groups[$class] = $rows;
        }

        return array_filter($groups);
    }
}
