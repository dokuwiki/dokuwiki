<?php

namespace dokuwiki\Utf8;

/**
 * Provides static access to the UTF-8 conversion tables
 *
 * Lazy-Loads tables on first access
 */
class Table
{

    /**
     * Get the upper to lower case conversion table
     *
     * @return array
     */
    public static function upperCaseToLowerCase()
    {
        static $table = null;
        if ($table === null) $table = include __DIR__ . '/tables/case.php';
        return $table;
    }

    /**
     * Get the lower to upper case conversion table
     *
     * @return array
     */
    public static function lowerCaseToUpperCase()
    {
        static $table = null;
        if ($table === null) {
            $uclc = self::upperCaseToLowerCase();
            $table = array_flip($uclc);
        }
        return $table;
    }

    /**
     * Get the lower case accent table
     * @return array
     */
    public static function lowerAccents()
    {
        static $table = null;
        if ($table === null) {
            $table = include __DIR__ . '/tables/loweraccents.php';
        }
        return $table;
    }

    /**
     * Get the lower case accent table
     * @return array
     */
    public static function upperAccents()
    {
        static $table = null;
        if ($table === null) {
            $table = include __DIR__ . '/tables/upperaccents.php';
        }
        return $table;
    }

    /**
     * Get the romanization table
     * @return array
     */
    public static function romanization()
    {
        static $table = null;
        if ($table === null) {
            $table = include __DIR__ . '/tables/romanization.php';
        }
        return $table;
    }

    /**
     * Get the special chars as a concatenated string
     * @return string
     */
    public static function specialChars()
    {
        static $string = null;
        if ($string === null) {
            $table = include __DIR__ . '/tables/specials.php';
            // FIXME should we cache this to file system?
            $string = Unicode::toUtf8($table);
        }
        return $string;
    }
}
