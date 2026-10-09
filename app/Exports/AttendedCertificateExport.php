<?php

namespace App\Exports;

use App\Models\Admin\Event;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class AttendedCertificateExport implements FromCollection, WithEvents, WithStyles, WithCustomStartCell
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
        $hasAttendedCol = Schema::hasColumn('tbl_registration', 'is_attended');
        $this->event = $event->load([
            'category',
            'registrations' => function ($q) use ($hasAttendedCol) {
                $q->where('status', 'paid')
                  ->when($hasAttendedCol, function ($sub) {
                      $sub->where(function ($nested) {
                          $nested->where('is_attended', true)
                                 ->orWhereNull('is_attended');
                      });
                  })
                  ->orderBy('id', 'asc');
            },
            'eventBeltFees',
        ]);
    }

    public function collection()
    {
        $categoryName = $this->event->category->name ?? 'Belt Exam';

        $this->records = $this->event->registrations->map(function ($reg) use ($categoryName) {
            $submittedData = is_array($reg->submitted_data) 
                ? $reg->submitted_data 
                : json_decode($reg->submitted_data ?? '[]', true);

            $getVal = function ($keyword) use ($submittedData) {
                $field = collect($submittedData)->first(
                    fn($item) => str_contains(strtolower($item['label'] ?? ''), strtolower($keyword))
                );
                return is_array($field['value'] ?? '')
                    ? implode(', ', $field['value'])
                    : ($field['value'] ?? '');
            };

            // Belt identification
            $beltId = $getVal('belt');
            $belt = null;
            if (!empty($beltId)) {
                try {
                    $belt = DB::table('tbl_belt')->where('id', $beltId)->first();
                } catch (\Throwable $e) {
                    $belt = null;
                }
            }
            // Prefer real belt names; leave blank (not "N/A") so certificate import can fall back
            $currentBelt = trim((string)($belt?->from_belt ?? ''));

            // Next belt - critical for certificate template selection
            $nextBeltField = collect($submittedData)->first(function ($item) {
                if (!isset($item['label'])) return false;
                $label = strtolower(trim($item['label']));
                $type = strtolower(trim($item['type'] ?? ''));
                if ($label === 'next belt') return true;
                if ($type === 'belt') return str_contains($label, 'next') && !str_contains($label, 'current');
                return false;
            });

            $nextBeltId = '';
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
            // Last resort for cert template matching: use current belt name
            if ($nextBelt === '') {
                $nextBelt = $currentBelt;
            }

            // Class group
            $class = $getVal('class');
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

            $school = $getVal('school') ?: $getVal('school name');
            $place = trim((string) ($this->event->venue ?? ''));

            // Rank - default to "Pass" for belt exams if empty
            $rank = !empty($reg->rank) ? $reg->rank : 'Pass';

            // Amount
            $amountPaid = (float)($reg->amount ?? 0);
            if ($amountPaid <= 0 && !empty($this->event->fee)) {
                $amountPaid = (float)$this->event->fee + (float)($this->event->additional_fee ?? 0);
            }
            $this->totalAmount += $amountPaid;

            $regDate = !empty($reg->created_at) 
                ? Carbon::parse($reg->created_at)->format('d M Y') 
                : (!empty($this->event->event_date) ? Carbon::parse($this->event->event_date)->format('d M Y') : 'N/A');

            return [
                'Registration ID' => $reg->registration_code,
                'Name'            => $getVal('name') ?: $getVal('student name'),
                'Class'           => $mappedClass,
                'School'          => $school ?: 'N/A',
                'Place'           => $place,
                'Current Belt'    => $currentBelt,
                'Next Belt'       => $nextBelt,
                'Rank'            => $rank,
                'Category'        => $categoryName,
                'Amount Paid'     => $amountPaid,
                'Date of Reg'     => $regDate,
                'Attendance'      => 'Attended',
            ];
        })->toArray();

        return new Collection([]);
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

                $headers = [
                    '#',
                    'Registration ID',
                    'Name',
                    'Class',
                    'School',
                    'Place',
                    'Current Belt',
                    'Next Belt',
                    'Rank',
                    'Category',
                    'Amount Paid',
                    'Date of Reg',
                    'Attendance',
                ];

                $totalCols = count($headers);
                $lastColLetter = chr(64 + $totalCols);

                // Row 1: Event Title
                $sheet->mergeCells("A{$rowIndex}:{$lastColLetter}{$rowIndex}");
                $sheet->setCellValue("A{$rowIndex}", strtoupper($this->event->title) . ' - ATTENDED PARTICIPANTS (CERTIFICATES)');
                $sheet->getStyle("A{$rowIndex}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '0D3B66']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $rowIndex++;

                // Row 2: Event Details
                $eventDate = $this->event->event_date ?? $this->event->date;
                $dateFormatted = !empty($eventDate) ? Carbon::parse($eventDate)->format('d M Y') : 'N/A';
                $categoryName = $this->event->category->name ?? 'N/A';
                $venue = $this->event->venue ?? 'N/A';

                $sheet->mergeCells("A{$rowIndex}:{$lastColLetter}{$rowIndex}");
                $sheet->setCellValue("A{$rowIndex}", "Category: {$categoryName} | Date: {$dateFormatted} | Venue: {$venue} | Ready for Certificate Import");
                $sheet->getStyle("A{$rowIndex}")->applyFromArray([
                    'font'      => ['italic' => true, 'size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $rowIndex += 2;

                $amountColLetter = 'K';
                foreach ($this->recordsByClass() as $className => $rows) {
                    $sheet->mergeCells("A{$rowIndex}:{$lastColLetter}{$rowIndex}");
                    $sheet->setCellValue("A{$rowIndex}", $className.' ('.count($rows).')');
                    $sheet->getStyle("A{$rowIndex}:{$lastColLetter}{$rowIndex}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0D3B66']],
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
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F52BA']],
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

                // Summary Row
                $summaryRow = $rowIndex;
                $prevColIndex = ord($amountColLetter) - 65;
                $beforeAmountCol = chr(64 + $prevColIndex);

                $sheet->mergeCells("A{$summaryRow}:{$beforeAmountCol}{$summaryRow}");
                $sheet->setCellValue("A{$summaryRow}", "Total Attended Participants: " . count($this->records));
                $sheet->setCellValue("{$amountColLetter}{$summaryRow}", $this->totalAmount);
                $sheet->getStyle("{$amountColLetter}{$summaryRow}")->getNumberFormat()->setFormatCode('₹#,##0.00');

                $sheet->getStyle("A{$summaryRow}:{$lastColLetter}{$summaryRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 12],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EBF5FB']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                    'borders'   => [
                        'top'    => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '0F52BA']],
                        'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['rgb' => '0F52BA']],
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
                        'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '0D3B66']],
                    ]);
                    $rowIndex++;

                    $sheet->setCellValue("A{$rowIndex}", "Belt");
                    $sheet->mergeCells("A{$rowIndex}:C{$rowIndex}");
                    $sheet->setCellValue("D{$rowIndex}", "Participant Count");
                    $sheet->getStyle("A{$rowIndex}:D{$rowIndex}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0D3B66']],
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

                // Auto-size columns
                foreach (range('A', $lastColLetter) as $c) {
                    $sheet->getColumnDimension($c)->setAutoSize(true);
                }
            }
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }

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
