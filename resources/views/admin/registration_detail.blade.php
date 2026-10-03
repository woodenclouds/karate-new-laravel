<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Registration Form</title>
    <style>
        @page {
            margin: 50px 30px 50px 30px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #000;
            border: #000 1px solid;
            padding: 20px;
        }

        footer {
            position: fixed;
            bottom: -60px;
            left: 0;
            right: 0;
            height: 60px;
            text-align: center;
            font-size: 10px;
            padding-top: 10px;
        }

        .container {
            position: relative;
            margin-top: 20px;
        }

        .entry-form-bar {
            background: #fff;
            border: 2px solid #000;
            padding: 4px 8px;
            font-weight: bold;
            display: inline-block;
            margin: 10px 0 5px;
        }

        .note {
            font-size: 10px;
            font-style: italic;
            text-align: center;
        }

        .section-title {
            background-color: #000;
            color: #fff;
            padding: 4px;
            font-weight: bold;
            margin-top: 10px;
            text-align: center;
        }

        .form-table {
            width: 80%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        .form-table td {
            padding: 4px 6px;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            width: 20%;
        }

        .value {
            border-bottom: 1px dotted #000;
            width: 30%;
        }

        .image-block {
            position: absolute;
            top: 130px;
            right: 30px;
            text-align: center;
        }

        .image-block img {
            height: 80px;
            border: 1px solid #000;
            margin-top: 5px;
        }

        .image-block span {
            font-size: 10px;
            font-weight: bold;
        }

        .office-use-table,
        .grade-result-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .office-use-table th,
        .office-use-table td,
        .grade-result-table th,
        .grade-result-table td {
            border: 1px solid #000;
            text-align: center;
            padding: 6px;
        }

        .instructor-block {
            font-size: 11px;
            margin-top: 20px;
            text-align: right;
        }
    </style>
</head>

<body>

    {{-- Centered Header --}}
    @if (Str::contains(Str::lower($event->category->name), 'competition'))
        <table style="width:100%; border-collapse:collapse; margin-bottom:10px;background-color:#2c2c2c;color:#fff">
            @php
                $logo = base64_encode(file_get_contents(public_path('assets/admin/images/karate_logo.png')));
            @endphp

            <td style="width:120px; text-align:left; vertical-align:middle;">
                <img src="data:image/png;base64,{{ $logo }}" alt="Mentors DoKarate Logo" style="width:100px;">
            </td>

            {{-- Title Block --}}
            <td style="text-align:left;font-family:'Times New Roman', Times, serif;">
                <h1 style="font-size:18px;">MENTORS SPORTS KARATE DO</h1>
                <h2 style="font-size:15px;"><strong>Affiliated with:</strong> Kerala Karate Association & Indian
                    Karate Federation
                    <strong>Recognized by:</strong> World Karate Federation, Asian Karate Federation,<br> and
                    International Olympic Committee, Karate Association of India
                </h2>
                <h2 style="margin:5px 0;">
                        {{ strtoupper('School Level Inter Club Karate Championship') . ' ' . date('Y') }}</h2>
                <p style="margin:3px 0;">
                        <strong>VENUE:</strong> {{ $event->venue }} |
                        <strong>TIME:</strong> {{ \Carbon\Carbon::parse($event->event_time)->format('h:i A') }} |
                        <strong>DATE:</strong> {{ \Carbon\Carbon::parse($event->start_date)->format('d.m.Y') }}
                </p>
            </td>
            </tr>
        </table>
                    

        {{-- Entry Form Bar --}}
        <div style="text-align:center; margin-top:10px;">
            <div class="entry-form-bar">PARTICIPANT INDIVIDUAL ENTRY FORM</div>
        </div>
    @elseif(Str::contains(Str::lower($event->category->name), 'kyu'))
        <table style="width:100%; border-collapse:collapse; margin-bottom:10px;background-color:#2c2c2c;color:#fff">
            @php
                $logo = base64_encode(file_get_contents(public_path('assets/admin/images/karate_logo.png')));
            @endphp

            <td style="width:120px; text-align:left; vertical-align:middle;">
                <img src="data:image/png;base64,{{ $logo }}" alt="Mentors DoKarate Logo" style="width:100px;">
            </td>

            {{-- Title Block --}}
            <td style="text-align:left;font-family:'Times New Roman', Times, serif;">
                <h1 style="font-size:18px;">MENTORS SPORTS KARATE DO</h1>
                <h2 style="font-size:15px;"><strong>Affiliated with:</strong> Kerala Karate Association & Indian
                    Karate Federation
                    <strong>Recognized by:</strong> World Karate Federation, Asian Karate Federation,<br> and
                    International Olympic Committee, Karate Association of India
                </h2>
            </td>
            </tr>
        </table>

        {{-- Entry Form Bar --}}
        <div style="text-align:center; margin-top:10px;">
            <div class="entry-form-bar">Registration for KYU Grading</div>
        </div>
    @endif

    <footer>
        <strong>MENTORS SPORTS KARATE - DO</strong><br>
        12/1324(16), ASK Plaza, Priyadarshini Road, Palakkad-678001, Kerala
    </footer>

    <div class="container">
        {{-- @if (Str::contains(strtolower($event->category->name ?? ''), 'kyu'))
            <div style="text-align: center;">
                <div class="entry-form-bar">Registration for KYU Grading</div>
                <div class="note"></div>
            </div>
        @else
            <div style="text-align: center;">
                <div class="entry-form-bar">Registration for {{ $event->title ?? 'Event' }}</div>
                <div class="note"></div>
            </div>
        @endif --}}



        {{-- Registration Fields One per Row --}}
        <table class="form-table">
            @php
                $filteredFields = collect($formData)
                    ->filter(fn($f) => !empty($f['label']) && ($f['type'] ?? '') !== 'file')
                    ->values();
            @endphp

            {{-- @foreach ($filteredFields as $field)
                <tr>
                    <td class="label">{{ strtoupper($field['label']) }}</td>
                    <td class="value">
                        {{ is_array($field['value']) ? implode(', ', $field['value']) : strtoupper($field['value']) }}
                    </td>
                </tr>
            @endforeach --}}
            <tr>
                <td class="label">REGISTRATION ID</td>
                <td class="value">{{ $registration->registration_code }}</td>
            </tr>
            @foreach ($filteredFields as $field)
                @if (!empty($field['label']) && ($field['type'] ?? '') !== 'file')
                    <tr>
                        <td class="label">{{ strtoupper($field['label']) }}</td>
                        <td class="value">
                            @php
                                $value = $field['value'];

                                // Handle 'belt' type: fetch from_belt from bl_belt table
                                if (($field['type'] ?? '') === 'belt') {
                                    $belt = \DB::table('tbl_belt')->where('id', $value)->first();
                                    $value = $belt->from_belt ?? '';
                                }

                                // Handle 'date' type: format as dd-mm-yyyy
                                elseif (($field['type'] ?? '') === 'date' && !empty($value)) {
                                    $timestamp = strtotime($value);
                                    if ($timestamp !== false) {
                                        $value = date('d-m-Y', $timestamp);
                                    }
                                }

                                // Handle array values
                                if (is_array($value)) {
                                    $value = implode(', ', $value);
                                } else {
                                    $value = strtoupper($value);
                                }
                            @endphp

                            {{ $value }}
                        </td>
                    </tr>
                @endif
            @endforeach

        </table>

        {{-- Office Use Section --}}
        {{-- Office Use Section - Show only for KYU Grading --}}
        @if (Str::contains(strtolower($event->category->name ?? ''), 'kyu'))
            <h4 style="text-align: center; margin-top: 20px;">For Office Use</h4>
            <table class="office-use-table">
                <tr>
                    <th>Exercise<br>20</th>
                    <th>Syllabus<br>20</th>
                    <th>Kihon<br>20</th>
                    <th>Kata<br>20</th>
                    <th>Kumite<br>20</th>
                    <th>Total<br>100</th>
                </tr>
                <tr>
                    <td style="height: 30px;">&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </table>

            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                <!-- Left Table: Result -->
                <table class="grade-result-table" style="border-collapse: collapse; width: 200px;">
                    <thead>
                        <tr>
                            <th colspan="4" style="text-align: center; background: #f2f2f2;">Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Pass</td>
                            <td>Fail</td>
                            <td>Retest</td>
                            <td>Other</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Right Table: Grade -->
                <table class="grade-result-table" style="border-collapse: collapse; width: 400px;">
                    <thead>
                        <tr>
                            <th colspan="6" style="text-align: center; background: #f2f2f2;">Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>A+</td>
                            <td>A</td>
                            <td>B+</td>
                            <td>B</td>
                            <td>C+</td>
                            <td>C</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif

        <div class="instructor-block">
            Branch Instructor<br><br>
            Coaches Contact Number : .................................................
        </div>
    </div>
</body>

</html>
