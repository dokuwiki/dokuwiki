<?php

namespace LesserPHP\Functions;

use Exception;
use LesserPHP\Utils\Asserts;
use LesserPHP\Utils\Color;
use LesserPHP\Utils\Util;

/**
 * Implements the Color Operation functions for LESS
 *
 * @todo inheritance from ColorChannels is only until we figure out how the alpha() method should work
 * @link https://lesscss.org/functions/#color-operations
 */
class ColorOperation extends ColorChannels
{
    /** @inheritdoc */
    public function getFunctions(): array
    {
        return [
            'saturate' => [$this, 'saturate'],
            'desaturate' => [$this, 'desaturate'],
            'lighten' => [$this, 'lighten'],
            'darken' => [$this, 'darken'],
            'fadein' => [$this, 'fadein'],
            'fadeout' => [$this, 'fadeout'],
            'fade' => [$this, 'fade'],
            'spin' => [$this, 'spin'],
            'mix' => [$this, 'mix'],
            'tint' => [$this, 'tint'],
            'shade' => [$this, 'shade'],
            //'greyscale' => [$this, 'greyscale'],
            'contrast' => [$this, 'contrast'],
        ];
    }


    /**
     * Increase the saturation of a color in the HSL color space by an absolute amount
     *
     * @link https://lesscss.org/functions/#color-operations-saturate
     * @throws Exception
     */
    public function saturate(array $args): array
    {
        [$color, $delta] = $this->colorArgs($args);

        $hsl = Color::toHSL($color);
        $hsl[2] = Util::clamp($hsl[2] + $delta, 100);
        return Color::toRGB($hsl);
    }

    /**
     * Decrease the saturation of a color in the HSL color space by an absolute amount
     *
     * @link https://lesscss.org/functions/#color-operations-desaturate
     * @throws Exception
     */
    public function desaturate(array $args): array
    {
        [$color, $delta] = $this->colorArgs($args);

        $hsl = Color::toHSL($color);
        $hsl[2] = Util::clamp($hsl[2] - $delta, 100);
        return Color::toRGB($hsl);
    }

    /**
     * Increase the lightness of a color in the HSL color space by an absolute amount
     *
     * @link https://lesscss.org/functions/#color-operations-lighten
     * @throws Exception
     */
    public function lighten(array $args): array
    {
        [$color, $delta] = $this->colorArgs($args);

        $hsl = Color::toHSL($color);
        $hsl[3] = Util::clamp($hsl[3] + $delta, 100);
        return Color::toRGB($hsl);
    }

    /**
     * Decrease the lightness of a color in the HSL color space by an absolute amount
     *
     * @link https://lesscss.org/functions/#color-operations-darken
     * @throws Exception
     */
    public function darken(array $args): array
    {
        [$color, $delta] = $this->colorArgs($args);

        $hsl = Color::toHSL($color);
        $hsl[3] = Util::clamp($hsl[3] - $delta, 100);
        return Color::toRGB($hsl);
    }

    /**
     * Decrease the transparency (or increase the opacity) of a color, making it more opaque
     *
     * @link https://lesscss.org/functions/#color-operations-fadein
     * @throws Exception
     */
    public function fadein(array $args): array
    {
        [$color, $delta] = $this->colorArgs($args);
        $color[4] = Util::clamp(($color[4] ?? 1) + $delta / 100);
        return $color;
    }

    /**
     * Increase the transparency (or decrease the opacity) of a color, making it less opaque
     *
     * @link https://lesscss.org/functions/#color-operations-fadeout
     * @throws Exception
     */
    public function fadeout(array $args): array
    {
        [$color, $delta] = $this->colorArgs($args);
        $color[4] = Util::clamp(($color[4] ?? 1) - $delta / 100);
        return $color;
    }

    /**
     * Set the absolute opacity of a color.
     * Can be applied to colors whether they already have an opacity value or not.
     *
     * @link https://lesscss.org/functions/#color-operations-fade
     * @throws Exception
     */
    public function fade(array $args): array
    {
        [$color, $alpha] = $this->colorArgs($args);
        $color[4] = Util::clamp($alpha / 100.0);
        return $color;
    }

    /**
     * Rotate the hue angle of a color in either direction
     *
     * @link https://lesscss.org/functions/#color-operations-spin
     * @throws Exception
     */
    public function spin(array $args): array
    {
        [$color, $delta] = $this->colorArgs($args);

        $hsl = Color::toHSL($color);

        $hsl[1] = $hsl[1] + $delta % 360;
        if ($hsl[1] < 0) $hsl[1] += 360;

        return Color::toRGB($hsl);
    }

    /**
     * mixes two colors by weight
     * mix(@color1, @color2, [@weight: 50%]);
     *
     * @link https://lesscss.org/functions/#color-operations-mix
     * @throws Exception
     */
    public function mix(array $args): array
    {
        if ($args[0] != 'list' || count($args[2]) < 2) {
            throw new Exception('mix expects (color1, color2, weight)');
        }

        [$first, $second] = $args[2];
        $first = Asserts::assertColor($first);
        $second = Asserts::assertColor($second);

        $first_a = $this->alpha($first);
        $second_a = $this->alpha($second);

        if (isset($args[2][2])) {
            $weight = $args[2][2][1] / 100.0;
        } else {
            $weight = 0.5;
        }

        $w = $weight * 2 - 1;
        $a = $first_a - $second_a;

        $w1 = (($w * $a == -1 ? $w : ($w + $a) / (1 + $w * $a)) + 1) / 2.0;
        $w2 = 1.0 - $w1;

        $new = [
            'color',
            $w1 * $first[1] + $w2 * $second[1],
            $w1 * $first[2] + $w2 * $second[2],
            $w1 * $first[3] + $w2 * $second[3],
        ];

        if ($first_a != 1.0 || $second_a != 1.0) {
            $new[] = $first_a * $weight + $second_a * ($weight - 1);
        }

        return Color::fixColor($new);
    }

    /**
     * Mix color with white in variable proportion.
     *
     * It is the same as calling `mix(#ffffff, @color, @weight)`.
     *
     *     tint(@color, [@weight: 50%]);
     *
     * @link https://lesscss.org/functions/#color-operations-tint
     * @throws Exception
     * @return array Color
     */
    public function tint(array $args): array
    {
        $white = ['color', 255, 255, 255];
        if ($args[0] == 'color') {
            return $this->mix(['list', ',', [$white, $args]]);
        } elseif ($args[0] == 'list' && count($args[2]) == 2) {
            return $this->mix([$args[0], $args[1], [$white, $args[2][0], $args[2][1]]]);
        } else {
            throw new Exception('tint expects (color, weight)');
        }
    }

    /**
     * Mix color with black in variable proportion.
     *
     * It is the same as calling `mix(#000000, @color, @weight)`
     *
     *     shade(@color, [@weight: 50%]);
     *
     * @link http://lesscss.org/functions/#color-operations-shade
     * @return array Color
     * @throws Exception
     */
    public function shade(array $args): array
    {
        $black = ['color', 0, 0, 0];
        if ($args[0] == 'color') {
            return $this->mix(['list', ',', [$black, $args]]);
        } elseif ($args[0] == 'list' && count($args[2]) == 2) {
            return $this->mix([$args[0], $args[1], [$black, $args[2][0], $args[2][1]]]);
        } else {
            throw new Exception('shade expects (color, weight)');
        }
    }

    // greyscale is missing

    /**
     * Choose which of two colors provides the greatest contrast with another
     *
     * @link https://lesscss.org/functions/#color-operations-contrast
     * @throws Exception
     */
    public function contrast(array $args): array
    {
        $darkColor = ['color', 0, 0, 0];
        $lightColor = ['color', 255, 255, 255];
        $threshold = 0.43;

        if ($args[0] == 'list') {
            $inputColor = (isset($args[2][0])) ? Asserts::assertColor($args[2][0]) : $lightColor;
            $darkColor = (isset($args[2][1])) ? Asserts::assertColor($args[2][1]) : $darkColor;
            $lightColor = (isset($args[2][2])) ? Asserts::assertColor($args[2][2]) : $lightColor;
            if (isset($args[2][3])) {
                if (isset($args[2][3][2]) && $args[2][3][2] == '%') {
                    $args[2][3][1] /= 100;
                    unset($args[2][3][2]);
                }
                $threshold = Asserts::assertNumber($args[2][3]);
            }
        } else {
            $inputColor = Asserts::assertColor($args);
        }

        $inputColor = Color::coerceColor($inputColor);
        $darkColor = Color::coerceColor($darkColor);
        $lightColor = Color::coerceColor($lightColor);

        //Figure out which is actually light and dark!
        if (Color::toLuma($darkColor) > Color::toLuma($lightColor)) {
            $t = $lightColor;
            $lightColor = $darkColor;
            $darkColor = $t;
        }

        $inputColor_alpha = $this->alpha($inputColor);
        if ((Color::toLuma($inputColor) * $inputColor_alpha) < $threshold) {
            return $lightColor;
        }
        return $darkColor;
    }


    /**
     * Helper function to get arguments for color manipulation functions.
     * takes a list that contains a color like thing and a percentage
     *
     * @fixme explanation needs to be improved
     * @throws Exception
     */
    protected function colorArgs(array $args): array
    {
        if ($args[0] != 'list' || count($args[2]) < 2) {
            return [['color', 0, 0, 0], 0];
        }
        [$color, $delta] = $args[2];
        $color = Asserts::assertColor($color);
        $delta = floatval($delta[1]);

        return [$color, $delta];
    }
}
