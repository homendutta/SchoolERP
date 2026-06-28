<?php

declare(strict_types=1);

namespace App\Platform\Foundation\Identity\Support;

/**
 * Minimal, dependency-free Code 128-B barcode renderer (SVG output). The
 * Identity platform renders barcodes dynamically — image files are never stored.
 */
final class Code128
{
    /** The 107 Code 128 bar/space width patterns (indices 0–106). */
    private const PATTERNS = [
        '212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213',
        '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132',
        '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211',
        '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313',
        '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331',
        '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111',
        '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214',
        '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111',
        '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141',
        '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141',
        '114131', '311141', '411131', '211412', '211214', '211232', '2331112',
    ];

    private const START_B = 104;

    private const STOP = 106;

    /**
     * Render a Code 128-B barcode as an SVG string.
     */
    public static function svg(string $value, int $height = 60, int $module = 2): string
    {
        $value = preg_replace('/[^\x20-\x7E]/', '', $value) ?? '';
        if ($value === '') {
            $value = '0';
        }

        $codes = [self::START_B];
        $checksum = self::START_B;
        $position = 1;

        foreach (str_split($value) as $char) {
            $code = ord($char) - 32;
            $codes[] = $code;
            $checksum += $code * $position;
            $position++;
        }

        $codes[] = $checksum % 103;
        $codes[] = self::STOP;

        $x = 10;
        $bars = '';
        foreach ($codes as $code) {
            $pattern = self::PATTERNS[$code];
            $isBar = true;
            foreach (str_split($pattern) as $widthDigit) {
                $width = (int) $widthDigit * $module;
                if ($isBar) {
                    $bars .= '<rect x="'.$x.'" y="0" width="'.$width.'" height="'.$height.'" fill="#000"/>';
                }
                $x += $width;
                $isBar = ! $isBar;
            }
        }

        $totalWidth = $x + 10;

        return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$totalWidth.'" height="'.($height + 20)
            .'" viewBox="0 0 '.$totalWidth.' '.($height + 20).'">'
            .'<rect width="100%" height="100%" fill="#fff"/>'.$bars
            .'<text x="'.($totalWidth / 2).'" y="'.($height + 15)
            .'" font-family="monospace" font-size="12" text-anchor="middle">'.htmlspecialchars($value).'</text>'
            .'</svg>';
    }
}
