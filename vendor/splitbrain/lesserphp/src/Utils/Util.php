<?php

namespace LesserPHP\Utils;

use Exception;
use LesserPHP\Constants;

/**
 * Miscelaneous utility methods
 */
class Util
{
    /**
     * Clamps a value between a minimum and maximum value.
     *
     * This function takes a value and two boundary values (maximum and minimum).
     * It ensures that the provided value does not exceed the boundaries.
     * If the value is less than the minimum, the minimum is returned.
     * If the value is greater than the maximum, the maximum is returned.
     * Otherwise, the original value is returned.
     */
    public static function clamp(float $v, float $max = 1, float $min = 0): float
    {
        return min($max, max($min, $v));
    }

    /**
     * Quote a string for use in a regular expression
     */
    public static function pregQuote(string $what): string
    {
        return preg_quote($what, '/');
    }

    /**
     * Return a boolean type
     *
     * @param mixed $a
     */
    public static function toBool($a): array
    {
        if ($a) return Constants::TRUE;
        return Constants::FALSE;
    }

    /**
     * Converts numbers between different types of units
     *
     * @throws Exception
     */
    public static function convert($number, $to): array
    {
        $value = Asserts::assertNumber($number);
        $from = $number[2];

        // easy out
        if ($from == $to)
            return $number;

        // check if the from value is a length
        if (($from_index = array_search($from, Constants::LENGTH_UNITS)) !== false) {
            // make sure to value is too
            if (in_array($to, Constants::LENGTH_UNITS)) {
                // do the actual conversion
                $to_index = array_search($to, Constants::LENGTH_UNITS);
                $px = $value * Constants::LENGTH_BASES[$from_index];
                $result = $px * (1 / Constants::LENGTH_BASES[$to_index]);

                $result = round($result, 8);
                return ['number', $result, $to];
            }
        }

        // do the same check for times
        if (in_array($from, Constants::TIME_UNITS)) {
            if (in_array($to, Constants::TIME_UNITS)) {
                // currently only ms and s are valid
                if ($to == 'ms')
                    $result = $value * 1000;
                else $result = $value / 1000;

                $result = round($result, 8);
                return ['number', $result, $to];
            }
        }

        // lastly check for an angle
        if (in_array($from, Constants::ANGLE_UNITS)) {
            // convert whatever angle it is into degrees
            switch ($from) {
                case 'rad':
                    $deg = rad2deg($value);
                    break;
                case 'turn':
                    $deg = $value * 360;
                    break;
                case 'grad':
                    $deg = $value / (400 / 360);
                    break;
                default:
                    $deg = $value;
                    break;
            }

            // Then convert it from degrees into desired unit
            switch ($to) {
                case 'rad':
                    $result = deg2rad($deg);
                    break;
                case 'turn':
                    $result = $deg / 360;
                    break;
                case 'grad':
                    $result = $deg * (400 / 360);
                    break;
                default:
                    $result = $deg;
                    break;
            }

            $result = round($result, 8);
            return ['number', $result, $to];
        }

        // we don't know how to convert these
        throw new Exception("Cannot convert $from to $to");
    }
}
