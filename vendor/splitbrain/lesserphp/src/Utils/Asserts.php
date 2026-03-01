<?php

namespace LesserPHP\Utils;

use Exception;

class Asserts
{
    /**
     * Check that the number of arguments is correct and return them
     *
     * @throws Exception
     */
    public static function assertArgs($value, $expectedArgs, $name = '')
    {
        if ($expectedArgs == 1) {
            return $value;
        } else {
            if ($value[0] !== 'list' || $value[1] != ',') {
                throw new Exception('expecting list');
            }
            $values = $value[2];
            $numValues = count($values);
            if ($expectedArgs != $numValues) {
                if ($name) {
                    $name = $name . ': ';
                }

                throw new Exception("{$name}expecting $expectedArgs arguments, got $numValues");
            }

            return $values;
        }
    }

    /**
     * Check that the number of arguments is at least the expected number and return them
     *
     * @throws Exception
     */
    public static function assertMinArgs($value, $expectedMinArgs, $name = '')
    {
        if ($value[0] !== 'list' || $value[1] != ',') {
            throw new Exception('expecting list');
        }
        $values = $value[2];
        $numValues = count($values);
        if ($expectedMinArgs > $numValues) {
            if ($name) {
                $name = $name . ': ';
            }

            throw new Exception("$name expecting at least $expectedMinArgs arguments, got $numValues");
        }

        return $values;
    }

    /**
     * Checks that the value is a number and returns it as float
     *
     * @param array $value The parsed value triplet
     * @param string $error The error message to throw
     * @throws Exception
     */
    public static function assertNumber(array $value, string $error = 'expecting number'): float
    {
        if ($value[0] == 'number') return (float)$value[1];
        throw new Exception($error);
    }

    /**
     * @throws Exception
     */
    public static function assertColor(array $value, $error = 'expected color value'): array
    {
        $color = Color::coerceColor($value);
        if (is_null($color)) throw new Exception($error);
        return $color;
    }
}
