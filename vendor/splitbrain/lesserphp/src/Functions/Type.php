<?php

namespace LesserPHP\Functions;

use LesserPHP\Utils\Asserts;
use LesserPHP\Utils\Color;
use LesserPHP\Utils\Util;

/**
 * Implements the type functions for LESS
 *
 * @link https://lesscss.org/functions/#type-functions
 */
class Type extends AbstractFunctionCollection
{
    /** @inheritdoc  */
    public function getFunctions(): array
    {
        return [
            'isnumber' => [$this, 'isnumber'],
            'isstring' => [$this, 'isstring'],
            'iscolor' => [$this, 'iscolor'],
            'iskeyword' => [$this, 'iskeyword'],
            'isurl' => [$this, 'isurl'],
            'ispixel' => [$this, 'ispixel'],
            'isem' => [$this, 'isem'],
            'isrem' => [$this, 'isrem'],
            'ispercentage' => [$this, 'ispercentage'],
            'isunit' => [$this, 'isunit'],
            //'isruleset' => [$this, 'isruleset'],
            //'isdefined' => [$this, 'isdefined'],
        ];
    }


    /**
     * Returns true if a value is a number, false otherwise
     *
     * @link https://lesscss.org/functions/#type-functions-isnumber
     */
    public function isnumber(array $value): array
    {
        return Util::toBool($value[0] == 'number');
    }

    /**
     * Returns true if a value is a string, false otherwise
     *
     * @link https://lesscss.org/functions/#type-functions-isstring
     */
    public function isstring(array $value): array
    {
        return Util::toBool($value[0] == 'string');
    }

    /**
     * Returns true if a value is a color, false otherwise
     *
     * @link https://lesscss.org/functions/#type-functions-iscolor
     */
    public function iscolor(array $value): array
    {
        return Util::toBool(Color::coerceColor($value));
    }

    /**
     * Returns true if a value is a keyword, false otherwise
     *
     * @link https://lesscss.org/functions/#type-functions-iskeyword
     */
    public function iskeyword(array $value): array
    {
        return Util::toBool($value[0] == 'keyword');
    }

    /**
     * Returns true if a value is a url, false otherwise
     *
     * @link https://lesscss.org/functions/#type-functions-isurl
     */
    public function isurl(array $value): array
    {
        return Util::toBool($value[0] == 'function' && $value[1] == 'url');
    }

    /**
     * Returns true if a value is a number in pixels, false otherwise
     *
     * @link https://lesscss.org/functions/#type-functions-ispixel
     */
    public function ispixel(array $value): array
    {
        return Util::toBool($value[0] == 'number' && $value[2] == 'px');
    }

    /**
     * Returns true if a value is an em value, false otherwise
     *
     * @link https://lesscss.org/functions/#type-functions-isem
     */
    public function isem(array $value): array
    {
        return Util::toBool($value[0] == 'number' && $value[2] == 'em');
    }

    /**
     * Returns true if a value is an rem value, false otherwise
     *
     * This method does not exist in the official less.js implementation
     */
    public function isrem(array $value): array
    {
        return Util::toBool($value[0] == 'number' && $value[2] == 'rem');
    }

    /**
     * Returns true if a value is a percentage, false otherwise
     *
     * @link https://lesscss.org/functions/#type-functions-ispercentage
     */
    public function ispercentage(array $value): array
    {
        return Util::toBool($value[0] == 'number' && $value[2] == '%');
    }

    /**
     * Returns true if a value is a number with a given unit, false otherwise
     *
     * @link https://lesscss.org/functions/#type-functions-isunit
     */
    public function isunit(array $args): array
    {
        [$input, $unit] = Asserts::assertArgs($args, 2, 'isunit');
        $unit = $this->lessc->compileValue($this->lessc->unwrap($unit));

        return Util::toBool(
            $input[0] == 'number' &&
            $input[2] == $unit
        );
    }

    // isruleset is missing
    // isdefined is missing
}
