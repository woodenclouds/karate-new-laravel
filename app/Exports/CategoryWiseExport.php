<?php

namespace App\Exports;

use App\Models\Admin\Event;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Maatwebsite\Excel\Events\AfterSheet;
use Razorpay\Api\Api;
use Carbon\Carbon;

class CategoryWiseExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithEvents
{
    protected Event $event;
    protected string $categoryName;
    protected string $status;

    public function __construct(Event $event, string $categoryName = '', string $status = 'paid')
    {
        $this->status = $status;
        $this->event = $event->load([
            'registrations' => fn($q) => $q->where('status', $status)->orderBy('id', 'asc')
        ]);
        $this->categoryName = $categoryName ?: ($event->category->name ?? 'All');
    }

    public function collection()
    {
        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

        return $this->event->registrations->map(function ($reg) use ($api) {
            // Helper function to get field value
            $getVal = function ($keyword) use ($reg) {
                $field = collect($reg->submitted_data)->first(
                    fn($item) => str_contains(strtolower($item['label']), strtolower($keyword))
                );
                return is_array($field['value'] ?? '')
                    ? implode(', ', $field['value'])
                    : ($field['value'] ?? '');
            };

            // Get values
            $name = $getVal('name');
            $school = $getVal('school');
            $phone = $getVal('phone') ?: $getVal('contact') ?: $getVal('mobile') ?: 'N/A';

            // Get amount from database or Razorpay
            $amount = $reg->amount ?? 0;
            if ($amount == 0 && !empty($reg->payment_id)) {
                try {
                    $payment = $api->payment->fetch($reg->payment_id);
                    $amount = $payment->amount / 100;
                } catch (\Exception $e) {
                    $amount = 0;
                }
            }

            return [
                'ID' => $reg->registration_code,
                'Name' => $name,
                'School' => $school,
                'Amount' => $amount,
                'Phone Number' => $phone,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'School',
            'Amount',
            'Phone Number',
        ];
    }

    public function title(): string
    {
        return $this->categoryName;
    }

    public function styles(Worksheet $sheet)
    {
        // Styles will be applied in AfterSheet event after inserting event info rows
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Insert rows at the top for event information
                $sheet->insertNewRowBefore(1, 4);
                
                // Format event date
                $eventDate = $this->event->event_date 
                    ? Carbon::parse($this->event->event_date)->format('d-m-Y') 
                    : 'N/A';
                
                // Add Event Title
                $sheet->setCellValue('A1', 'Event Title:');
                $sheet->setCellValue('B1', $this->event->title);
                $sheet->mergeCells('B1:E1');
                
                // Add Event Date
                $sheet->setCellValue('A2', 'Event Date:');
                $sheet->setCellValue('B2', $eventDate);
                $sheet->mergeCells('B2:E2');
                
                // Add Venue
                $sheet->setCellValue('A3', 'Venue:');
                $sheet->setCellValue('B3', $this->event->venue ?? 'N/A');
                $sheet->mergeCells('B3:E3');
                
                // Style the event information rows
                $sheet->getStyle('A1:A3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E7E6E6']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                
                $sheet->getStyle('B1:E3')->applyFromArray([
                    'font' => [
                        'size' => 11,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                
                // Add border to event info section
                $sheet->getStyle('A1:E3')->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
                
                // Style the header row (now row 5)
                $sheet->getStyle('A5:E5')->applyFromArray([
                    'font' => [
                        'bold' => true, 
                        'size' => 12,
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
                
                // Auto-size columns
                foreach (range('A', 'E') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
                
                // Add borders to all data cells (starting from row 5)
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A5:' . $highestColumn . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
            },
        ];
    }
}

