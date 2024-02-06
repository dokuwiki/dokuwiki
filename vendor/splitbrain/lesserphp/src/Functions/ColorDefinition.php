<?php

namespace LesserPHP\Functions;

use Exception;
use LesserPHP\Utils\Asserts;

/**
 * Implements the color definition functions of LESS
 *
 * @link https://lesscss.org/functions/#color-definition
 */
class ColorDefinition extends AbstractFunctionCollection
{
    /** @inheritdoc */
    public function getFunctions(): array
    {
        return [
            //'rgb' => [$this, 'rgb'],
            //'rgba' => [$this, 'rgba'],
            'rgbahex' => [$this, 'rgbahex'],
            'argb' => [$this, 'argb'],
            //'hsl' => [$this, 'hsl'],
            //'hsla' => [$this, 'hsla'],
            //'hsv' => [$this, 'hsv'],
            //'hsva' => [$this, 'hsva'],
        ];
    }

    // rgb is missing
    // rgba is missing

    /**
     * Creates a hex representation of a color in #AARRGGBB format (NOT #RRGGBBAA!)
     *
     * This method does not exist in the official less.js implementation
     * @see lib_argb
     * @throws Exception
     */
    public function rgbahex(array $color): string
    {
        $color = Asserts::assertColor($color);

        return sprintf(
            '#%02x%02x%02x%02x',
            isset($color[4]) ? $color[4] * 255 : 255,
            $color[1],
            $color[2],
            $color[3]
        );
    }

    /**
     * Creates a hex representation of a color in #AARRGGBB format (NOT #RRGGBBAA!)
     *
     * @https://lesscss.org/functions/#color-definition-argb
     * @throws Exception
     */
    public function argb(array $color): string
    {
        return $this->rgbahex($color);
    }

    // hsl is missing

    // hsla is missing

    // hsv is missing

    // hsva is missing
}
