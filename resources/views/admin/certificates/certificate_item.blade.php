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

    // Box widths (cm) per field, matching the CSS in certificate_pdf / certificates_bulk_pdf
    $boxWidths = [
        'belt'         => ['name' => 14.0, 'place' => 14.0],
        'competition'  => ['name' => 13.0, 'place' => 13.0, 'category' => 13.0],
        'championship' => ['name' => 12.0, 'place' => 8.8, 'category' => 13.0, 'placed_in' => 9.8],
        'intro_school' => ['name' => 12.0, 'place' => 8.8, 'category' => 13.0, 'placed_in' => 9.8],
    ];

    // Shrinks the font (down to 8pt) so the text fits its box; wraps to two lines only if still too long.
    $fitStyle = function (string $field, string $text, string $size, string $weight) use ($boxWidths, $certificateType) {
        $width = $boxWidths[$certificateType][$field] ?? null;
        $startPt = (float) $size;
        if ($width === null || $startPt <= 0 || trim($text) === '') {
            return "font-size: {$size};";
        }
        $fit = \App\Support\CertificateText::fit($text, $width, $startPt, $weight === 'bold', 8.0);
        $css = 'font-size: ' . rtrim(rtrim(number_format($fit['size'], 1, '.', ''), '0'), '.') . 'pt;';
        if (!$fit['fits']) {
            $css .= " white-space: normal; width: {$width}cm; max-width: {$width}cm; line-height: 1.05;";
        }
        return $css;
    };
@endphp

<div class="certificate-container {{ $certificateClass }}">
    <!-- Background Template Image -->
    @if($backgroundImage)
        <img src="{{ $backgroundImage }}" alt="Certificate Background" class="background-image">
    @endif

    <!-- Dynamic Content Overlay -->
    <div class="content">
        <!-- Name Field - NAME 名前 -->
        @php
            $printedName = !empty($participant['name']) ? strtoupper(trim($participant['name'])) : '';
        @endphp
        <div class="dynamic-field field-name" style="color: {{ $nameColor }}; {{ $fitStyle('name', $printedName, $nameSize, $nameBold) }} font-weight: {{ $nameBold }};">
            {{ $printedName }}
        </div>

        <!-- Place Field - venue from the event, exact spelling -->
        @php
            if (is_object($certificate) && method_exists($certificate, 'certificateVenue')) {
                $printedVenue = $certificate->certificateVenue();
            } else {
                $printedVenue = trim((string) ($certificate->venue ?? ''));
            }
        @endphp
        <div class="dynamic-field field-place" style="color: {{ $placeColor }}; {{ $fitStyle('place', $printedVenue, $placeSize, $placeBold) }} font-weight: {{ $placeBold }};">
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

        <!-- Category Field - CATEGORY -->
        @if(in_array($certificateType, ['competition', 'intro_school', 'championship']))
            @php
                $printedCategory = !empty($participant['category']) ? strtoupper(trim($participant['category'])) : '';
            @endphp
            <div class="dynamic-field field-category" style="color: {{ $categoryColor }}; {{ $fitStyle('category', $printedCategory, $categorySize, $categoryBold) }} font-weight: {{ $categoryBold }};">
                {{ $printedCategory }}
            </div>
        @endif

        <!-- Placed In Field (e.g., Team Kumite, Kata) for Championship and Intro School -->
        @if(in_array($certificateType, ['intro_school', 'championship']))
            @php
                $placedInVal = !empty($participant['placed_in']) ? $participant['placed_in'] : ($participant['class'] ?? '');
            @endphp
            @if(!empty(trim($placedInVal)))
                <div class="dynamic-field field-placed-in" style="color: {{ $nameColor }}; {{ $fitStyle('placed_in', strtoupper(trim($placedInVal)), '13pt', 'bold') }} font-weight: bold;">
                    {{ strtoupper(trim($placedInVal)) }}
                </div>
            @endif
        @endif

        <!-- Date of Registration Field - DATE OF REG. 登録日 -->
        <div class="dynamic-field field-date-reg" style="color: {{ $dateColor }}; font-size: {{ $dateSize }}; font-weight: {{ $dateBold }};">
            {{ \App\Models\Certificate::formatDate($participant['date_of_reg'] ?? null, $certificate->event_date ?? null) }}
        </div>
    </div>
</div>
