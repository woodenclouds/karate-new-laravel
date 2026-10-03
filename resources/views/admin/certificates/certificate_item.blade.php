@php
    $certificateType = strtolower($certificate->certificate_type ?? 'belt');
    $certificateClass = 'certificate-' . $certificateType;

    // Style configuration with defaults: name, place, date, and reg no bold as requested
    $customStyles = $customStyles ?? [];
    $nameColor = $customStyles['name_color'] ?? ($certificateType === 'belt' ? '#c00' : '#000');
    $nameSize = $customStyles['name_size'] ?? '15pt';
    $nameBold = isset($customStyles['name_bold']) ? ($customStyles['name_bold'] ? 'bold' : 'normal') : 'bold';

    $placeColor = $customStyles['place_color'] ?? '#000';
    $placeSize = $customStyles['place_size'] ?? '14pt';
    $placeBold = isset($customStyles['place_bold']) ? ($customStyles['place_bold'] ? 'bold' : 'normal') : 'bold';

    $regNoColor = $customStyles['reg_no_color'] ?? '#000';
    $regNoSize = $customStyles['reg_no_size'] ?? '14pt';
    $regNoBold = isset($customStyles['reg_no_bold']) ? ($customStyles['reg_no_bold'] ? 'bold' : 'normal') : 'bold';

    $dateColor = $customStyles['date_color'] ?? '#000';
    $dateSize = $customStyles['date_size'] ?? '14pt';
    $dateBold = isset($customStyles['date_bold']) ? ($customStyles['date_bold'] ? 'bold' : 'normal') : 'bold';

    $categoryColor = $customStyles['category_color'] ?? '#000';
    $categorySize = $customStyles['category_size'] ?? '14pt';
    $categoryBold = isset($customStyles['category_bold']) ? ($customStyles['category_bold'] ? 'bold' : 'normal') : 'bold';
@endphp

<div class="certificate-container {{ $certificateClass }}">
    <!-- Background Template Image -->
    @if($backgroundImage)
        <img src="{{ $backgroundImage }}" alt="Certificate Background" class="background-image">
    @endif

    <!-- Dynamic Content Overlay -->
    <div class="content">
        <!-- Name Field - NAME 名前 -->
        <div class="dynamic-field field-name" style="color: {{ $nameColor }}; font-size: {{ $nameSize }}; font-weight: {{ $nameBold }};">
            {{ isset($participant['name']) && !empty($participant['name']) ? strtoupper(trim($participant['name'])) : '' }}
        </div>

        <!-- Place Field - venue from the event, exact spelling -->
        @php
            if (is_object($certificate) && method_exists($certificate, 'certificateVenue')) {
                $printedVenue = $certificate->certificateVenue();
            } else {
                $printedVenue = trim((string) ($certificate->venue ?? ''));
            }
        @endphp
        <div class="dynamic-field field-place" style="color: {{ $placeColor }}; font-size: {{ $placeSize }}; font-weight: {{ $placeBold }};">
            {{ $printedVenue }}
        </div>

        <!-- Registration Number Field - REG. NO 登録番号 (Only for Belt certificates) -->
        @if($certificateType === 'belt')
            @php
                $regId = $participant['registration_id'] ?? '';
                $regNo = '';
                if (!empty(trim($regId))) {
                    $regNo = preg_replace('/^MENTORS/i', '', trim($regId));
                    $regNo = preg_replace('/\D/', '', $regNo);
                    if (strlen($regNo) > 4) $regNo = substr($regNo, -4);
                    $regNo = str_pad($regNo, 4, '0', STR_PAD_LEFT);
                }
            @endphp
            <div class="dynamic-field field-reg-no" style="color: {{ $regNoColor }}; font-size: {{ $regNoSize }}; font-weight: {{ $regNoBold }};">
                {{ $regNo }}
            </div>
        @endif

        <!-- Category Field - CATEGORY (Only for Competition certificates) -->
        @if($certificateType === 'competition')
            <div class="dynamic-field field-category" style="color: {{ $categoryColor }}; font-size: {{ $categorySize }}; font-weight: {{ $categoryBold }};">
                {{ isset($participant['category']) && !empty($participant['category']) ? strtoupper(trim($participant['category'])) : '' }}
            </div>
        @endif

        <!-- Date of Registration Field - DATE OF REG. 登録日 -->
        <div class="dynamic-field field-date-reg" style="color: {{ $dateColor }}; font-size: {{ $dateSize }}; font-weight: {{ $dateBold }};">
            @if(isset($participant['date_of_reg']) && !empty($participant['date_of_reg']))
                @php
                    $dateStr = trim($participant['date_of_reg']);
                    try {
                        $dateStr = preg_replace('/\s+/', ' ', $dateStr);
                        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $dateStr)) {
                            $date = \Carbon\Carbon::createFromFormat('Y-m-d', substr($dateStr, 0, 10));
                            echo $date->format('d M Y');
                        } elseif (preg_match('/\d{1,2}\/\d{1,2}\/\d{4}/', $dateStr, $matches)) {
                            $date = \Carbon\Carbon::createFromFormat('d/m/Y', $matches[0]);
                            echo $date->format('d M Y');
                        } else {
                            $date = \Carbon\Carbon::parse($dateStr);
                            echo $date->format('d M Y');
                        }
                    } catch (\Exception $e) {
                        $cleanDate = preg_replace('/[^0-9\-\/ ]/', '', $dateStr);
                        echo $cleanDate ?: $dateStr;
                    }
                @endphp
            @elseif(isset($certificate->event_date) && !empty($certificate->event_date))
                {{ \Carbon\Carbon::parse($certificate->event_date)->format('d M Y') }}
            @endif
        </div>
    </div>
</div>
