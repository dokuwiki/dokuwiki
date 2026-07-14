<?php

namespace LesserPHP\Utils;

use LesserPHP\Constants;

/**
 * Color handling utilities
 */
class Color
{
    /**
     * coerce a value for use in color operation
     * returns null if the value can't be used in color operations
     */
    public static function coerceColor(array $value): ?array
    {
        switch ($value[0]) {
            case 'color':
                return $value;
            case 'raw_color':
                $c = ['color', 0, 0, 0];
                $colorStr = substr($value[1], 1);
                $num = hexdec($colorStr);
                $width = strlen($colorStr) == 3 ? 16 : 256;

                for ($i = 3; $i > 0; $i--) { // 3 2 1
                    $t = (int) $num % $width;
                    $num /= $width;

                    $c[$i] = $t * (256 / $width) + $t * floor(16 / $width);
                }

                return $c;
            case 'keyword':
                $name = $value[1];
                if (isset(Constants::CSS_COLORS[$name])) {
                    $rgba = explode(',', Constants::CSS_COLORS[$name]);

                    if (isset($rgba[3]))
                        return ['color', $rgba[0], $rgba[1], $rgba[2], $rgba[3]];

                    return ['color', $rgba[0], $rgba[1], $rgba[2]];
                }
                return null;
        }
        return null;
    }

    /**
     * Calculate the perceptual brightness of a color object
     */
    public static function toLuma(array $color): float
    {
        [, $r, $g, $b] = Color::coerceColor($color);

        $r = $r / 255;
        $g = $g / 255;
        $b = $b / 255;

        $r = ($r <= 0.03928) ? $r / 12.92 : (($r + 0.055) / 1.055) ** 2.4;
        $g = ($g <= 0.03928) ? $g / 12.92 : (($g + 0.055) / 1.055) ** 2.4;
        $b = ($b <= 0.03928) ? $b / 12.92 : (($b + 0.055) / 1.055) ** 2.4;

        return (0.2126 * $r) + (0.7152 * $g) + (0.0722 * $b);
    }

    /**
     * Convert a color to HSL color space
     */
    public static function toHSL(array $color): array
    {
        if ($color[0] == 'hsl') return $color;

        $r = $color[1] / 255;
        $g = $color[2] / 255;
        $b = $color[3] / 255;

        $min = min($r, $g, $b);
        $max = max($r, $g, $b);

        $L = ($min + $max) / 2;
        if ($min == $max) {
            $S = $H = 0;
        } else {
            if ($L < 0.5) {
                $S = ($max - $min) / ($max + $min);
            } else {
                $S = ($max - $min) / (2.0 - $max - $min);
            }

            if ($r == $max) {
                $H = ($g - $b) / ($max - $min);
            } elseif ($g == $max) {
                $H = 2.0 + ($b - $r) / ($max - $min);
            } elseif ($b == $max) {
                $H = 4.0 + ($r - $g) / ($max - $min);
            } else {
                $H = 0;
            }
        }

        $out = [
            'hsl',
            ($H < 0 ? $H + 6 : $H) * 60,
            $S * 100,
            $L * 100,
        ];

        if (count($color) > 4) $out[] = $color[4]; // copy alpha
        return $out;
    }


    /**
     * Converts a hsl array into a color value in rgb.
     * Expects H to be in range of 0 to 360, S and L in 0 to 100
     */
    public static function toRGB(array $color): array
    {
        if ($color[0] == 'color') return $color;

        $H = $color[1] / 360;
        $S = $color[2] / 100;
        $L = $color[3] / 100;

        if ($S == 0) {
            $r = $g = $b = $L;
        } else {
            $temp2 = $L < 0.5 ?
                $L * (1.0 + $S) :
                $L + $S - $L * $S;

            $temp1 = 2.0 * $L - $temp2;

            $r = self::calculateRGBComponent($H + 1 / 3, $temp1, $temp2);
            $g = self::calculateRGBComponent($H, $temp1, $temp2);
            $b = self::calculateRGBComponent($H - 1 / 3, $temp1, $temp2);
        }

        // $out = array('color', round($r*255), round($g*255), round($b*255));
        $out = ['color', $r * 255, $g * 255, $b * 255];
        if (count($color) > 4) $out[] = $color[4]; // copy alpha
        return $out;
    }


    /**
     * make sure a color's components don't go out of bounds
     */
    public static function fixColor(array $c): array
    {
        foreach (range(1, 3) as $i) {
            if ($c[$i] < 0) $c[$i] = 0;
            if ($c[$i] > 255) $c[$i] = 255;
        }

        return $c;
    }

    /**
     * Helper function for the HSL to RGB conversion process.
     *
     * This function normalizes the input component of the HSL color and determines the RGB
     * value based on the HSL values.
     *
     * @param float $comp The component of the HSL color to be normalized and converted.
     * @param float $temp1 The first temporary variable used in the conversion process
     * @param float $temp2 The second temporary variable used in the conversion process
     *
     * @return float The calculated RGB value as percentage of the maximum value (255)
     */
    protected static function calculateRGBComponent(float $comp, float $temp1, float $temp2): float
    {
        // Normalize the component value to be within the range [0, 1]
        if ($comp < 0) $comp += 1.0;
        elseif ($comp > 1) $comp -= 1.0;

        // Determine the return value based on the value of the component
        if (6 * $comp < 1) return $temp1 + ($temp2 - $temp1) * 6 * $comp;
        if (2 * $comp < 1) return $temp2;
        if (3 * $comp < 2) return $temp1 + ($temp2 - $temp1) * ((2 / 3) - $comp) * 6;

        // Fallback return value, represents the case where the saturation of the color is zero
        return $temp1;
    }
}
