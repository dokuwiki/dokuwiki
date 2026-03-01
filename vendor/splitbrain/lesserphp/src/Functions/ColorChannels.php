<?php

namespace LesserPHP\Functions;

use Exception;
use LesserPHP\Utils\Asserts;
use LesserPHP\Utils\Color;

/**
 * Implementation of the Color Channel functions for LESS
 *
 * @link https://lesscss.org/functions/#color-channels
 */
class ColorChannels extends AbstractFunctionCollection
{
    /** @inheritdoc */
    public function getFunctions(): array
    {
        return [
            'hue' => [$this, 'hue'],
            'saturation' => [$this, 'saturation'],
            'lightness' => [$this, 'lightness'],
            //'hsvhue' => [$this, 'hsvhue'],
            //'hsvsaturation' => [$this, 'hsvsaturation'],
            //'hsvvalue' => [$this, 'hsvvalue'],
            'red' => [$this, 'red'],
            'green' => [$this, 'green'],
            'blue' => [$this, 'blue'],
            'alpha' => [$this, 'alpha'],
            'luma' => [$this, 'luma'],
            //'luminance' => [$this, 'luminance'],
        ];
    }

    /**
     * Extracts the hue channel of a color object in the HSL color space
     *
     * @link https://lesscss.org/functions/#color-channel-hue
     * @throws Exception
     */
    public function hue(array $color): int
    {
        $hsl = Color::toHSL(Asserts::assertColor($color));
        return round($hsl[1]);
    }

    /**
     * Extracts the saturation channel of a color object in the HSL color space
     *
     * @link https://lesscss.org/functions/#color-channel-saturation
     * @throws Exception
     */
    public function saturation(array $color): int
    {
        $hsl = Color::toHSL(Asserts::assertColor($color));
        return round($hsl[2]);
    }

    /**
     * Extracts the lightness channel of a color object in the HSL color space
     *
     * @link https://lesscss.org/functions/#color-channel-lightness
     * @throws Exception
     */
    public function lightness(array $color): int
    {
        $hsl = Color::toHSL(Asserts::assertColor($color));
        return round($hsl[3]);
    }

    // hsvhue is missing

    // hsvsaturation is missing

    // hsvvalue is missing

    /**
     * @throws Exception
     */
    public function red($color)
    {
        $color = Asserts::assertColor($color);
        return $color[1];
    }

    /**
     * @throws Exception
     */
    public function green($color)
    {
        $color = Asserts::assertColor($color);
        return $color[2];
    }

    /**
     * @throws Exception
     */
    public function blue($color)
    {
        $color = Asserts::assertColor($color);
        return $color[3];
    }

    /**
     * Extracts the alpha channel of a color object
     *
     * defaults to 1 for colors without an alpha
     * @fixme non-colors return null - should they?
     * @link https://lesscss.org/functions/#color-channel-alpha
     */
    public function alpha(array $value): ?float
    {
        if (!is_null($color = Color::coerceColor($value))) {
            return $color[4] ?? 1;
        }
        return null;
    }

    /**
     * Calculates the luma (perceptual brightness) of a color object
     *
     * @link https://lesscss.org/functions/#color-channel-luma
     * @throws Exception
     */
    public function luma(array $color): array
    {
        $color = Asserts::assertColor($color);
        return ['number', round(Color::toLuma($color) * 100, 8), '%'];
    }

    // luminance is missing
}
