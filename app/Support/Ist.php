<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;

class Ist
{
    public const ZONE = 'Asia/Kolkata';

    public static function format(mixed $value, string $format = 'd M Y h:i A'): string
    {
        $date = self::parse($value);

        if ($date === null) {
            return '';
        }

        return $date->timezone(self::ZONE)->format($format).' IST';
    }

    public static function parse(mixed $value): ?CarbonInterface
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::createFromTimestamp((int) $value, 'UTC');
        }

        if ($value instanceof CarbonInterface) {
            return $value->copy();
        }

        return Carbon::parse($value, config('app.timezone'));
    }
}
