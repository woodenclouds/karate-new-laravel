<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\Event;
use App\Models\CertificateTemplate;

class Certificate extends Model
{
    protected $table = 'tbl_certificate';

    protected $fillable = [
        'event_id',
        'event_title',
        'event_date',
        'venue',
        'certificate_type',
        'template_id',
        'excel_file_path',
        'participants_data',
        'certificate_count',
        'logo',
    ];

    protected $casts = [
        'event_date' => 'date',
        'participants_data' => 'array',
        'certificate_count' => 'integer',
    ];

    public function template()
    {
        return $this->belongsTo(CertificateTemplate::class, 'template_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    /**
     * Venue printed on the certificate: the linked event's venue, otherwise the stored copy.
     */
    public function certificateVenue(): string
    {
        if ($this->event_id) {
            $fromEvent = trim((string) ($this->event->venue ?? ''));
            if ($fromEvent !== '') {
                return $fromEvent;
            }
        }

        return trim((string) ($this->venue ?? ''));
    }

    /**
     * Format a certificate date with the full month name in words (e.g. "15 January 2026").
     * Handles Excel numeric serial dates, DD/MM/YYYY, DD-MM-YYYY, DD/MM/YY, YYYY-MM-DD, and Carbon strings.
     */
    public static function formatDate(mixed $value, mixed $fallback = null): string
    {
        $parsed = self::parseCertificateDate($value);
        if ($parsed !== null) {
            return $parsed->format('d/m/Y');
        }

        if ($fallback !== null) {
            $fallbackParsed = self::parseCertificateDate($fallback);
            if ($fallbackParsed !== null) {
                return $fallbackParsed->format('d/m/Y');
            }
        }

        $raw = trim((string) ($value ?? ''));
        return ($raw !== '' && strtolower($raw) !== 'n/a') ? $raw : '';
    }

    /**
     * Parse various date representations into a Carbon instance.
     */
    public static function parseCertificateDate(mixed $value): ?\Carbon\Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return \Carbon\Carbon::instance($value);
        }

        $dateStr = trim((string) $value);
        if ($dateStr === '' || in_array(strtolower($dateStr), ['n/a', 'na', '-', 'null', 'none', '—'], true)) {
            return null;
        }

        $dateStr = preg_replace('/\s+/', ' ', $dateStr);

        // 1. Excel serial date number (e.g. 46037 or 46324.5)
        if (is_numeric($dateStr) && (float) $dateStr > 10000 && (float) $dateStr < 100000) {
            try {
                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $dateStr);
                return \Carbon\Carbon::instance($dt);
            } catch (\Throwable $e) {
                // continue
            }
        }

        // 2. YYYY-MM-DD, YYYY/MM/DD, or YYYY.MM.DD (with optional time)
        if (preg_match('/^(\d{4})[\-\/\.](\d{1,2})[\-\/\.](\d{1,2})\b/', $dateStr, $m)) {
            $year = (int) $m[1];
            $month = (int) $m[2];
            $day = (int) $m[3];
            if ($month > 12 && $day <= 12) {
                [$month, $day] = [$day, $month];
            }
            if (checkdate($month, $day, $year)) {
                return \Carbon\Carbon::createFromDate($year, $month, $day);
            }
        }

        // 3. DD/MM/YYYY, DD-MM-YYYY, DD.MM.YYYY, or 2-digit year DD/MM/YY, DD-MM-YY
        if (preg_match('/^(\d{1,2})[\-\/\.](\d{1,2})[\-\/\.](\d{2}|\d{4})\b/', $dateStr, $m)) {
            $first = (int) $m[1];
            $second = (int) $m[2];
            $year = (int) $m[3];
            if ($year < 100) {
                $year += ($year < 70) ? 2000 : 1900;
            }
            if ($second > 12 && $first <= 12) {
                $month = $first;
                $day = $second;
            } else {
                $day = $first;
                $month = $second;
            }
            if (checkdate($month, $day, $year)) {
                return \Carbon\Carbon::createFromDate($year, $month, $day);
            }
        }

        // 4. General Carbon parse (e.g. "21 Sep 2026 12:32 PM IST", "17 Sep 2026")
        try {
            $cleaned = trim(preg_replace('/\bIST\b/i', '', $dateStr));
            return \Carbon\Carbon::parse($cleaned);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
