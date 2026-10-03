<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>KYU Grading Registration</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            padding: 30px;
            margin: 0;
        }

        .custom-header {
            text-align: center;
            border: 2px solid #000;
            padding: 15px 20px;
            background: #1e1e1e;
            color: #fff;
        }

        .custom-header h1 {
            font-size: 18px;
            font-weight: 900;
            text-transform: uppercase;
            margin: 0 0 6px 0;
        }

        .custom-header p {
            margin: 3px 0;
            font-size: 12px;
            font-weight: 500;
        }

        .entry-form-bar {
            background: #fff;
            border: 2px solid #000;
            padding: 6px 10px;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }

        .note {
            font-size: 11px;
            margin-top: 3px;
            font-style: italic;
            color: #000;
            text-align: center;
        }

        .section-title {
            background-color: #000;
            color: #fff;
            padding: 6px;
            font-weight: bold;
            margin-top: 20px;
            text-align: center;
        }

        .container {
            width: 100%;
            border: 1px solid #000;
            padding: 20px;
            position: relative;
        }

        .form-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .form-table td {
            padding: 6px 10px;
            vertical-align: top;
        }

        .label {
            width: 30%;
            font-weight: bold;
        }

        .value {
            border-bottom: 1px dotted #333;
            width: 70%;
        }

        .footer {
            text-align: center;
            font-size: 11px;
            margin-top: 40px;
            border-top: 1px solid #000;
            padding-top: 10px;
        }

        .image-block {
            position: absolute;
            top: 250px;
            /* Adjust based on section-title position */
            right: 30px;
            text-align: center;
        }

        .image-block img {
            height: 100px;
            border: 1px solid #000;
            margin-top: 5px;
        }

        .image-block span {
            font-size: 11px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">

        {{-- Centered Header --}}
        @if (Str::contains(Str::lower($event->category->name), 'competition'))
            <table style="width:100%; border-collapse:collapse; margin-bottom:10px;">
                <tr>
                    {{-- Logo on Left --}}
                    <td style="width:120px; text-align:left; vertical-align:middle;">
                        <img src="{{ public_path('assets/admin/images/karate_logo.png') }}" alt="Mentors DoKarate Logo"
                            style="width:100px;">
                    </td>

                    {{-- Title Block --}}
                    <td style="text-align:left;font-family:'Times New Roman', Times, serif;">
                        <h1 style="margin:0;">MENTORS SPORTS KARATE DO</h1>
                        <h2 style="font-size:15px;"><strong>Affiliated with:</strong> Kerala Karate Association & Indian
                            Karate Federation
                            <strong>Recognized by:</strong> World Karate Federation, Asian Karate Federation,<br> and
                            International
                            Olympic Committee, Karate Association of India
                        </h2>
                        <h2 style="margin: 0;font-size:15px;">
                            {{ strtoupper('School Level Inter Club Karate Championship') . ' ' . date('Y') }}</h2>
                        <p style="margin:0;font-size:15px;"><strong>VENUE:</strong> {{ $event->venue }} |
                            <strong>TIME:</strong> {{ \Carbon\Carbon::parse($event->event_time)->format('h:i A') }} |
                            <strong>DATE:</strong>
                            {{ \Carbon\Carbon::parse($event->start_date)->format('d.m.Y') }}
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
                <tr>
                    {{-- Logo on Left --}}
                    <td style="width:120px; text-align:left; vertical-align:middle;">
                        <img src="{{ public_path('assets/admin/images/karate_logo.png') }}" alt="Mentors DoKarate Logo"
                            style="height:100px;">
                    </td>

                    {{-- Title Block --}}
                    <td style="text-align:left;font-family:'Times New Roman', Times, serif;">
                        <h1 style="font-size:18px;">MENTORS SPORTS KARATE DO</h1>
                        <h2 style="font-size:15px;"><strong>Affiliated with:</strong> Kerala Karate Association & Indian
                            Karate Federation
                            <strong>Recognized by:</strong> World Karate Federation, Asian Karate Federation,<br> and
                            International
                            Olympic Committee, Karate Association of India
                        </h2>
                    </td>
                </tr>
            </table>

            {{-- Entry Form Bar --}}
            <div style="text-align:center; margin-top:10px;">
                <div class="entry-form-bar">Registration for KYU Grading</div>
            </div>
        @endif


        {{-- Section Title --}}
        {{-- <div class="section-title">REGISTRATION DETAILS</div> --}}

        {{-- Candidate Image on Right Side --}}
        @if (isset($formData))
            @foreach ($formData as $field)
                @if (($field['type'] ?? '') === 'file' && !empty($field['value']))
                    <div class="image-block">
                        {{-- <span>Candidate Image</span><br> --}}
                        <img src="{{ public_path(str_replace('public/', 'storage/', $field['value'])) }}"
                            alt="Candidate Image">
                    </div>
                    @break
                @endif
            @endforeach
        @endif

        {{-- Registration Fields --}}
        {{-- <table class="form-table">
            @foreach ($formData as $field)
                @if (!empty($field['label']) && ($field['type'] ?? '') !== 'file')
                    <tr>
                        <td class="label">{{ strtoupper($field['label']) }}</td>
                        <td class="value">
                            @php $value = strtoupper($field['value']) ?? ''; @endphp
                            {{ is_array($value) ? implode(', ', $value) : $value }}
                        </td>
                    </tr>
                @endif
            @endforeach
        </table> --}}
        <table class="form-table">
            {{-- ✅ Show Registration Code --}}
            <tr>
                <td class="label">REGISTRATION ID</td>
                <td class="value">{{ $registration->registration_code }}</td>
            </tr>
            @foreach ($formData as $field)
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


        {{-- Footer --}}
        <div class="footer">
            <strong>MENTORS SPORTS KARATE - DO</strong><br>
            12/1324(16), ASK Plaza, Priyadarshini Road, Palakkad-678001, Kerala
        </div>

    </div>
</body>

</html>
