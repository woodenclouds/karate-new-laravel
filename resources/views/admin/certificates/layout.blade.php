<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate of Merit</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Merriweather:wght@400;700&family=Dancing+Script:wght@400;700&display=swap');

        body {
            margin: 0;
            padding: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f0f0f0;
            font-family: 'Merriweather', serif;
        }

        .certificate-container {
            width: 8.5in;
            height: 11in;
            background-color: #fff;
            background-image: url('{{ asset("images/certificate-border.png") }}');
            background-size: 100% 100%;
            background-repeat: no-repeat;
            padding: 0.8in;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            color: #333;
        }

        .header-section {
            margin-top: 0.5in;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .msma-logo {
            width: 140px;
            margin-bottom: 10px;
        }

        .japanese-text {
            font-size: 1.2em;
            font-weight: bold;
        }

        .main-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.8em;
            font-weight: 700;
            color: #c00;
        }

        .affiliation-text {
            font-size: 0.7em;
            line-height: 1.4;
            margin-bottom: 20px;
        }

        .logos-row img {
            height: 35px;
            margin-bottom: 30px;
        }

        .certificate-title-section {
            margin-bottom: 20px;
        }

        .certificate-of-merit {
            font-family: 'Dancing Script', cursive;
            font-size: 3.2em;
            font-weight: 700;
        }

        .certificate-of-merit-japanese {
            font-size: 1.8em;
            font-weight: bold;
        }

        .certify-text {
            margin: 15px 0;
            font-size: 1.1em;
        }

        .fill-in-section {
            width: 90%;
            font-size: 1em;
            text-align: left;
            margin-bottom: 30px;
        }

        .fill-in-line {
            margin-bottom: 15px;
        }

        .championship-title {
            text-align: center;
            font-family: 'Playfair Display', serif;
            font-size: 1.5em;
            font-weight: 700;
            margin: 20px 0;
        }

        .signatures-section {
            width: 90%;
            display: flex;
            justify-content: space-between;
            margin-top: auto;
            font-size: 0.8em;
            line-height: 1.4;
        }

        .signature-block {
            text-align: center;
            flex: 1;
        }

        .signature-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
<div class="certificate-container">
    <!-- Header -->
    <div class="header-section">
        <img src="{{ asset('assets/admin/images/karate_logo.png') }}" alt="MSMA Logo" class="msma-logo">
        <div class="japanese-text">メンターズスポーツ空手道 インド</div>
        <div class="main-title">MENTORS SPORTS KARATE-DO INDIA</div>
        <div class="affiliation-text">
            AFFILIATED WITH: KARATE ORGANISATION OF INDIA (KIO)<br>
            RECOGNISED BY: WORLD KARATE FEDERATION (WKF), ASIAN KARATE FEDERATION (AKF)<br>
            INTERNATIONAL OLYMPIC COMMITTEE (IOC)
        </div>
    </div>

    <!-- Title -->
    <div class="certificate-title-section">
        <div class="certificate-of-merit">Certificate of Merit</div>
        <div class="certificate-of-merit-japanese">證券 メリット</div>
    </div>

    <div class="certify-text">This is to Certify that</div>
<!-- Participant Info -->
<div class="fill-in-section" style="text-align: center;">
    <div class="fill-in-line" style="margin-bottom: 15px; font-size: 1.05em;">
        <strong>Master/Miss:</strong>
        <span style="font-weight: 500;">
            {{ collect($registration->submitted_data)->firstWhere('label', 'name')['value'] ?? 'N/A' }}
        </span>
        has actively participated in the
    </div>

    <div class="championship-title" style="font-size: 1.7em; margin-bottom: 20px; color: #b30000;">
        {{ $registration->event->title }}
    </div>

    <div class="fill-in-line" style="margin-bottom: 10px;">
        <strong>Category:</strong>
        <span style="font-weight: 500;">
            {{ ucfirst($registration->event->category->name ?? 'N/A') }}
        </span>
        &nbsp;&nbsp;&nbsp;
        <strong>School:</strong>
        <span style="font-weight: 500;">
            {{ collect($registration->submitted_data)->firstWhere('label', 'school')['value'] ?? 'N/A' }}
        </span>
    </div>

    <div class="fill-in-line" style="margin-bottom: 10px;">
        <strong>Date:</strong>
        <span style="font-weight: 500;">
            {{ \Carbon\Carbon::parse($registration->event->event_date)->format('d M Y') }}
        </span>
        &nbsp;&nbsp;&nbsp;
        <strong>Venue:</strong>
        <span style="font-weight: 500;">
            {{ $registration->event->venue }}
        </span>
    </div>

    @php
        $categoryName = strtolower($registration->event->category->name ?? '');
    @endphp

    @if ($categoryName === 'competition')
        <div class="fill-in-line" style="margin-bottom: 10px;">
            <strong>Result:</strong>
            <span style="font-weight: 500;">
                {{ collect($registration->submitted_data)->firstWhere('label', 'result')['value'] ?? 'Participation' }}
            </span>
        </div>
    @elseif ($categoryName === 'kyu grading')
        <div class="fill-in-line" style="margin-bottom: 10px;">
            <strong>Grade Achieved:</strong>
            <span style="font-weight: 500;">
                {{ collect($registration->submitted_data)->firstWhere('label', 'grade')['value'] ?? 'N/A' }}
            </span>
        </div>
    @elseif ($categoryName === 'camping')
        <div class="fill-in-line" style="margin-bottom: 10px;">
            <strong>Camp Location:</strong>
            <span style="font-weight: 500;">
                {{ collect($registration->submitted_data)->firstWhere('label', 'location')['value'] ?? 'N/A' }}
            </span>
        </div>
    @endif
</div>

    <!-- Signatures -->
    <div class="signatures-section">
        <div class="signature-block">
            <div class="signature-name">MANOJ. G</div>
            <div>Black Belt 5th Dan-Japan</div>
            <div>Instructor MSMA-INDIA</div>
            <div>Representative India</div>
            <div>MSMA Official Examiner India</div>
        </div>
        <div class="signature-block">
            <div class="signature-name">VINIJA B</div>
            <div>Secretary MSMA-INDIA</div>
        </div>
        <div class="signature-block">
            <div class="signature-name">SUJEESH GEORGE K.C</div>
            <div>Chairman of MSMA-INDIA</div>
        </div>
    </div>
</div>
</body>
</html>
