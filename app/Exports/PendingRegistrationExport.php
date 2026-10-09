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
use App\Support\Ist;
use Carbon\Carbon;

class PendingRegistrationExport implements FromCollection, WithEvents, WithStyles, WithCustomStartCell
{
    use Exportable;

    protected Event $event;
    protected array $records = [];
    protected float $totalPendingAmount = 0;

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
            'registrations' => fn($q) => $q->where('status', '!=', 'paid')->orderBy('id', 'asc'),
            'eventBeltFees',
        ]);
    }

    public function collection()
    {
        $this->records = $this->event->registrations->map(function ($reg) {
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

            // Phone number
            $phone = $getVal('phone') ?: $getVal('mobile') ?: $getVal('contact') ?: $getVal('whatsapp') ?: 'N/A';

            // Belt details
            $beltField = collect($submittedData)->first(
                fn($item) => str_contains(strtolower($item['label'] ?? ''), 'belt')
            );
            $beltId = is_array($beltField['value'] ?? '')
                ? ($beltField['value'][0] ?? null)
                : ($beltField['value'] ?? null);

            $belt = null;
            if (!empty($beltId)) {
                try {
                    $belt = DB::table('tbl_belt')->where('id', $beltId)->first();
                } catch (\Throwable $e) {
                    $belt = null;
                }
            }
            $currentBelt = $belt?->from_belt ?? 'N/A';

            // Next belt
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

            if (!empty($nextBeltId) && is_numeric($nextBeltId)) {
                try {
                    $nextBelt = DB::table('tbl_belt')->where('id', $nextBeltId)->value('from_belt') ?? 'N/A';
                } catch (\Throwable $e) {
                    $nextBelt = 'N/A';
                }
            } else {
                $nextBelt = $belt?->to_belt ?? 'N/A';
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

            // Calculate pending amount
            $calcAmount = (float)($reg->amount ?? 0);
            if ($calcAmount <= 0) {
                if ($beltId) {
                    try {
                        $eventBeltFee = DB::table('event_belt_fees')
                            ->where('event_id', $this->event->id)
                            ->where('belt_id', $beltId)
                            ->first();
                        if ($eventBeltFee && $eventBeltFee->fee > 0) {
                            $calcAmount = (float)$eventBeltFee->fee;
                        } elseif ($belt && $belt->fees > 0) {
                            $calcAmount = (float)$belt->fees;
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }
                if ($calcAmount <= 0) {
                    $calcAmount = (float)($this->event->fee ?? 0) + (float)($this->event->additional_fee ?? 0);
                }
            }

            $this->totalPendingAmount += $calcAmount;

            return [
                'Registration ID' => $reg->registration_code,
                'Name'            => $getVal('name') ?: $getVal('student name'),
                'Class'           => $mappedClass,
                'School'          => $getVal('school') ?: $getVal('school name'),
                'Phone Number'    => $phone,
                'Current Belt'    => $currentBelt,
                'Next Belt'       => $nextBelt,
                'Amount Pending'  => $calcAmount,
                'Status'          => ucfirst($reg->status ?? 'pending'),
                'Entered By'      => ucfirst($reg->entered_by ?? 'user'),
                'Submitted At'    => Ist::format($reg->created_at),
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
                    'Phone Number',
                    'Current Belt',
                    'Next Belt',
                    'Amount Pending',
                    'Status',
                    'Entered By',
                    'Submitted At',
                ];

                $totalCols = count($headers);
                $lastColLetter = chr(64 + $totalCols);

                // Row 1: Event Title
                $sheet->mergeCells("A{$rowIndex}:{$lastColLetter}{$rowIndex}");
                $sheet->setCellValue("A{$rowIndex}", strtoupper($this->event->title) . ' - PENDING REGISTRATIONS');
                $sheet->getStyle("A{$rowIndex}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '8A1F11']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $rowIndex++;

                // Row 2: Event details
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

                // Row 4: Table Headers
                $col = 'A';
                foreach ($headers as $head) {
                    $sheet->setCellValue($col . $rowIndex, $head);
                    $col++;
                }

                $sheet->getStyle("A{$rowIndex}:{$lastColLetter}{$rowIndex}")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C0392B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension($rowIndex)->setRowHeight(24);
                $rowIndex++;

                // Data Rows
                $startDataRow = $rowIndex;
                $amountColLetter = 'I';

                foreach ($this->records as $index => $rec) {
                    $col = 'A';
                    foreach ($headers as $key) {
                        if ($key === '#') {
                            $sheet->setCellValue($col . $rowIndex, $index + 1);
                            $sheet->getStyle($col . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        } elseif ($key === 'Amount Pending') {
                            $amountColLetter = $col;
                            $sheet->setCellValue($col . $rowIndex, $rec['Amount Pending'] ?? 0);
                            $sheet->getStyle($col . $rowIndex)->getNumberFormat()->setFormatCode('₹#,##0.00');
                        } else {
                            $sheet->setCellValue($col . $rowIndex, $rec[$key] ?? '');
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

                // Summary Row
                $summaryRow = $rowIndex;
                $prevColIndex = ord($amountColLetter) - 65;
                $beforeAmountCol = chr(64 + $prevColIndex);

                $sheet->mergeCells("A{$summaryRow}:{$beforeAmountCol}{$summaryRow}");
                $sheet->setCellValue("A{$summaryRow}", "Total Pending Registrations: " . count($this->records));
                $sheet->setCellValue("{$amountColLetter}{$summaryRow}", $this->totalPendingAmount);
                $sheet->getStyle("{$amountColLetter}{$summaryRow}")->getNumberFormat()->setFormatCode('₹#,##0.00');

                $sheet->getStyle("A{$summaryRow}:{$lastColLetter}{$summaryRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 12],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FDEDEC']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                    'borders'   => [
                        'top'    => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'C0392B']],
                        'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['rgb' => 'C0392B']],
                    ],
                ]);
                $sheet->getRowDimension($summaryRow)->setRowHeight(22);

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
}
