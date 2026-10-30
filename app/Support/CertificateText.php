<?php

namespace App\Support;

use Dompdf\Dompdf;

class CertificateText
{
    private const CM_TO_PT = 28.3464567;

    private static ?Dompdf $dompdf = null;

    /**
     * Width of text in points for Times (regular or bold) at the given size.
     */
    public static function textWidth(string $text, float $sizePt, bool $bold = false): float
    {
        try {
            self::$dompdf ??= new Dompdf();
            return (float) self::$dompdf->getFontMetrics()
                ->getTextWidth($text, $bold ? 'times-bold' : 'times', $sizePt);
        } catch (\Throwable $e) {
            // Rough fallback: average Times glyph is ~0.5em wide
            return mb_strlen($text) * $sizePt * ($bold ? 0.55 : 0.5);
        }
    }

    /**
     * Largest font size (pt) not above $startPt, and not below $minPt,
     * at which $text fits within $boxWidthCm.
     *
     * @return array{size: float, fits: bool}
     */
    public static function fit(string $text, float $boxWidthCm, float $startPt, bool $bold = false, float $minPt = 8.0): array
    {
        $text = trim($text);
        $maxWidth = $boxWidthCm * self::CM_TO_PT * 0.98; // small safety margin
        $size = $startPt;

        if ($text === '') {
            return ['size' => $size, 'fits' => true];
        }

        while ($size > $minPt && self::textWidth($text, $size, $bold) > $maxWidth) {
            $size -= 0.5;
        }
        $size = max($size, $minPt);

        return [
            'size' => $size,
            'fits' => self::textWidth($text, $size, $bold) <= $maxWidth,
        ];
    }
}

