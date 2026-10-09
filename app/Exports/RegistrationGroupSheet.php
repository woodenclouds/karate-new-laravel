<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Models\Admin\belt;
use App\Support\Ist;


class RegistrationGroupSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $title;
    protected $registrations;
    protected $categoryType; // 👈 to check if competition or kyu

    public function __construct($title, Collection $registrations, string $categoryType)
    {
        $this->title = $title;
        $this->registrations = $registrations;
        $this->categoryType = strtolower($categoryType);
    }

    public function collection()
    {
        return $this->registrations
            ->filter(fn($reg) => $reg->status === 'paid') // ✅ double safety
            ->map(function ($reg) {
                $getVal = function ($keyword) use ($reg) {
                    $field = collect($reg->submitted_data)->first(
                        fn($item) => str_contains(strtolower($item['label']), strtolower($keyword))
                    );
                    return is_array($field['value'] ?? '') 
                        ? implode(', ', $field['value']) 
                        : ($field['value'] ?? '');
                };

                $beltId = $getVal('belt');
                $beltRecord = \App\Models\Admin\belt::find($beltId);
                $beltName = $beltRecord?->from_belt ?? 'N/A';

                // Get Next Belt: use form value if present, otherwise derive from current belt (e.g. White -> Yellow)
                $nextBeltId = '';
                $nextBeltField = collect($reg->submitted_data)->first(function ($item) {
                    if (!isset($item['label']) || !isset($item['type'])) {
                        return false;
                    }
                    $label = strtolower(trim($item['label']));
                    $type = strtolower(trim($item['type']));
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

                if (!empty($nextBeltId) && is_numeric($nextBeltId)) {
                    $nextBeltName = \App\Models\Admin\belt::find($nextBeltId)?->from_belt ?? 'N/A';
                } else {
                    // Derive next belt from current belt using tbl_belt.to_belt
                    $nextBeltName = $beltRecord?->to_belt ?? 'N/A';
                }

                $row = [
                    'Regisration ID' => $reg->registration_code,
                    'Name'        => $getVal('name'),
                    'Class'       => $getVal('class'),
                    'School'      => $getVal('school'),
                    'Current Belt'=> $beltName,
                    'Next Belt'   => $nextBeltName,
                    'Submitted At'=> Ist::format($reg->created_at),
                ];

                if ($this->categoryType === 'competition') {
                    $row['Weight'] = $getVal('weight');
                }

                return $row;
            });
    }


    public function headings(): array
    {
        $headings = [
            'Name',
            'Class',
            'School',
            'Current Belt',
            'Next Belt',
            'Submitted At',
        ];

        // Add Weight column ONLY for competition category
        if ($this->categoryType === 'competition') {
            $headings[] = 'Weight';
        }

        return $headings;
    }

    public function title(): string
    {
        return $this->title;
    }
}
