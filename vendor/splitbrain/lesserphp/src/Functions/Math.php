<?php

namespace LesserPHP\Functions;

use Exception;
use LesserPHP\Constants;
use LesserPHP\Utils\Asserts;
use LesserPHP\Utils\Util;

/**
 * Implements the math functions for LESS
 *
 * @link https://lesscss.org/functions/#math-functions
 */
class Math extends AbstractFunctionCollection
{
    /** @inheritdoc */
    public function getFunctions(): array
    {
        return [
            'ceil' => [$this, 'ceil'],
            'floor' => [$this, 'floor'],
            'percentage' => [$this, 'percentage'],
            'round' => [$this, 'round'],
            'sqrt' => [$this, 'sqrt'],
            'abs' => [$this, 'abs'],
            'sin' => [$this, 'sin'],
            'asin' => [$this, 'asin'],
            'cos' => [$this, 'cos'],
            'acos' => [$this, 'acos'],
            'tan' => [$this, 'tan'],
            'atan' => [$this, 'atan'],
            'pi' => [$this, 'pi'],
            'pow' => [$this, 'pow'],
            'mod' => [$this, 'mod'],
            'min' => [$this, 'min'],
            'max' => [$this, 'max'],
        ];
    }


    /**
     * Rounds up to the next highest integer
     *
     * @link https://lesscss.org/functions/#math-functions-ceil
     * @throws Exception
     */
    public function ceil(array $arg): array
    {
        $value = Asserts::assertNumber($arg);
        return ['number', ceil($value), $arg[2]];
    }

    /**
     * Rounds down to the next lowest integer
     *
     * @link https://lesscss.org/functions/#math-functions-floor
     * @throws Exception
     */
    public function floor(array $arg): array
    {
        $value = Asserts::assertNumber($arg);
        return ['number', floor($value), $arg[2]];
    }

    /**
     * Converts a floating point number into a percentage string
     *
     * @link https://lesscss.org/functions/#math-functions-percentage
     * @throws Exception
     */
    public function percentage(array $arg): array
    {
        $num = Asserts::assertNumber($arg);
        return ['number', $num * 100, '%'];
    }

    /**
     * Applies rounding
     *
     * @link https://lesscss.org/functions/#math-functions-round
     * @throws Exception
     */
    public function round(array $arg): array
    {
        if ($arg[0] != 'list') {
            $value = Asserts::assertNumber($arg);
            return ['number', round($value), $arg[2]];
        } else {
            $value = Asserts::assertNumber($arg[2][0]);
            $precision = Asserts::assertNumber($arg[2][1]);
            return ['number', round($value, $precision), $arg[2][0][2]];
        }
    }

    /**
     * Calculates square root of a number
     *
     * @link https://lesscss.org/functions/#math-functions-sqrt
     * @throws Exception
     */
    public function sqrt(array $num): float
    {
        return sqrt(Asserts::assertNumber($num));
    }

    /**
     * Calculates absolute value of a number. Keeps units as they are.
     *
     * @link https://lesscss.org/functions/#math-functions-abs
     * @throws Exception
     */
    public function abs(array $num): array
    {
        return ['number', abs(Asserts::assertNumber($num)), $num[2]];
    }

    /**
     * Calculates sine function
     *
     * @link https://lesscss.org/functions/#math-functions-sin
     * @throws Exception
     */
    public function sin(array $num): float
    {
        return sin(Asserts::assertNumber($num));
    }

    /**
     * Calculates arcsine function
     *
     * @link https://lesscss.org/functions/#math-functions-asin
     * @throws Exception
     */
    public function asin(array $num): array
    {
        $num = asin(Asserts::assertNumber($num));
        return ['number', $num, 'rad'];
    }

    /**
     * Calculates cosine function
     *
     * @link https://lesscss.org/functions/#math-functions-cos
     * @throws Exception
     */
    public function cos(array $num): float
    {
        return cos(Asserts::assertNumber($num));
    }

    /**
     * Calculates arccosine function
     *
     * @link https://lesscss.org/functions/#math-functions-acos
     * @throws Exception
     */
    public function acos(array $num): array
    {
        $num = acos(Asserts::assertNumber($num));
        return ['number', $num, 'rad'];
    }

    /**
     * Calculates tangent function
     *
     * @link https://lesscss.org/functions/#math-functions-tan
     * @throws Exception
     */
    public function tan(array $num): float
    {
        return tan(Asserts::assertNumber($num));
    }

    /**
     * Calculates arctangent function
     *
     * @link https://lesscss.org/functions/#math-functions-atan
     * @throws Exception
     */
    public function atan(array $num): array
    {
        $num = atan(Asserts::assertNumber($num));
        return ['number', $num, 'rad'];
    }

    /**
     * Return the value of pi
     *
     * @link https://lesscss.org/functions/#math-functions-pi
     */
    public function pi(): float
    {
        return pi();
    }

    /**
     * Returns the value of the first argument raised to the power of the second argument.
     *
     * @link https://lesscss.org/functions/#math-functions-pow
     * @throws Exception
     */
    public function pow(array $args): array
    {
        [$base, $exp] = Asserts::assertArgs($args, 2, 'pow');
        return ['number', Asserts::assertNumber($base) ** Asserts::assertNumber($exp), $args[2][0][2]];
    }

    /**
     * Returns the value of the first argument modulus second argument.
     *
     * @link https://lesscss.org/functions/#math-functions-mod
     * @throws Exception
     */
    public function mod(array $args): array
    {
        [$a, $b] = Asserts::assertArgs($args, 2, 'mod');
        return ['number', Asserts::assertNumber($a) % Asserts::assertNumber($b), $args[2][0][2]];
    }

    /**
     * Returns the lowest of one or more values
     *
     * @link https://lesscss.org/functions/#math-functions-min
     * @throws Exception
     */
    public function min(array $args): array
    {
        $values = Asserts::assertMinArgs($args, 1, 'min');

        $first_format = $values[0][2];

        $min_index = 0;
        $min_value = $values[0][1];

        for ($a = 0; $a < sizeof($values); $a++) {
            $converted = Util::convert($values[$a], $first_format);

            if ($converted[1] < $min_value) {
                $min_index = $a;
                $min_value = $values[$a][1];
            }
        }

        return $values[$min_index];
    }

    /**
     * Returns the highest of one or more values
     *
     * @link https://lesscss.org/functions/#math-functions-max
     * @throws Exception
     */
    public function max(array $args): array
    {
        $values = Asserts::assertMinArgs($args, 1, 'max');

        $first_format = $values[0][2];

        $max_index = 0;
        $max_value = $values[0][1];

        for ($a = 0; $a < sizeof($values); $a++) {
            $converted = Util::convert($values[$a], $first_format);

            if ($converted[1] > $max_value) {
                $max_index = $a;
                $max_value = $values[$a][1];
            }
        }

        return $values[$max_index];
    }
}
