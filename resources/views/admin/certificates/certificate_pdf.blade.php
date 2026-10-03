<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate of Diploma</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            margin: 0;
            padding: 0;
            size: {{ (isset($certType) && $certType === 'competition') || (isset($certificate->certificate_type) && strtolower($certificate->certificate_type) === 'competition') ? '21cm 29.7cm' : '24cm 33cm' }};
        }

        html, body {
            width: 100%;
            height: 100%;
            font-family: 'Times New Roman', Times, 'Times Roman', serif;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }

        .certificate-container {
            position: relative;
            width: 100%;
            height: 100%;
            background: #fff;
            overflow: hidden;
            page-break-inside: avoid;
        }

        /* Certificate Dimensions fill 100% of the exact @page */
        .certificate-belt,
        .certificate-competition {
            width: 100%;
            height: 100%;
        }

        /* Background Image covers the whole canvas */
        .background-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        /* Content Container - Overlay on template */
        .content {
            position: absolute;
            top: 0;
            left: 0;
            z-index: 10;
            width: 100%;
            height: 100%;
            padding: 0;
        }

        /* Dynamic Fields - Positioned absolutely to match template with top-left reference */
        .dynamic-field {
            position: absolute;
            font-family: 'Times New Roman', Times, 'Times Roman', serif;
            color: #000;
            font-weight: normal;
            white-space: nowrap;
            line-height: 1.2;
            text-align: left;
        }

        /* ============================================
           BELT CERTIFICATE POSITIONING (Top-Left aligned)
           ============================================ */
        .certificate-belt .field-name {
            top: 20.7cm;
            left: 5.6cm;
            font-size: 15pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #c00;
            max-width: 14cm;
            overflow: hidden;
            text-align: left;
        }

        .certificate-belt .field-place {
            top: 22.0cm;
            left: 5.6cm;
            font-size: 14pt;
            font-weight: bold;
            color: #000;
            max-width: 14cm;
            overflow: hidden;
            text-align: left;
        }

        .certificate-belt .field-reg-no {
            top: 23.3cm;
            left: 5.6cm;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
            max-width: 6.5cm;
            overflow: hidden;
            text-align: left;
        }

        .certificate-belt .field-date-reg {
            top: 23.3cm;
            left: 15.2cm;
            font-size: 14pt;
            font-weight: bold;
            color: #000;
            max-width: 8cm;
            overflow: hidden;
            text-align: left;
        }

        /* ============================================
           COMPETITION CERTIFICATE POSITIONING
           ============================================ */
        .certificate-competition .field-name {
            top: 16.9cm;
            left: 9.5cm;
            font-size: 13pt;
            font-weight: normal;
            text-transform: uppercase;
            color: #000;
            max-width: 13cm;
            overflow: hidden;
            text-align: left;
        }

        .certificate-competition .field-place {
            top: 20.6cm;
            left: 7.4cm;
            font-size: 13pt;
            font-weight: normal;
            color: #000;
            max-width: 13cm;
            overflow: hidden;
            text-align: left;
        }

        .certificate-competition .field-reg-no {
            display: none; /* Hide Reg No for competition certificates */
        }

        .certificate-competition .field-category {
            top: 19.5cm;
            left: 7.4cm;
            font-size: 13pt;
            font-weight: normal;
            text-transform: uppercase;
            color: #000;
            max-width: 13cm;
            overflow: hidden;
            text-align: left;
        }

        .certificate-competition .field-date-reg {
            top: 20.6cm;
            left: 3.9cm;
            font-size: 13pt;
            font-weight: normal;
            color: #000;
            max-width: 13cm;
            overflow: hidden;
            text-align: left;
        }
    </style>
</head>
<body>
    @include('admin.certificates.certificate_item', [
        'certificate' => $certificate,
        'participant' => $participant,
        'backgroundImage' => $backgroundImage,
        'customStyles' => $customStyles ?? []
    ])
</body>
</html>
