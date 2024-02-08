<?php

/**
 * compatibility functions
 *
 * This file contains a few functions that might be missing from the PHP build
 */

if (!function_exists('ctype_space')) {
    /**
     * Check for whitespace character(s)
     *
     * @param string $text
     * @return bool
     * @see ctype_space
     */
    function ctype_space($text)
    {
        if (!is_string($text)) return false; #FIXME original treats between -128 and 255 inclusive as ASCII chars
        if (trim($text) === '') return true;
        return false;
    }
}

if (!function_exists('ctype_digit')) {
    /**
     * Check for numeric character(s)
     *
     * @param string $text
     * @return bool
     * @see ctype_digit
     */
    function ctype_digit($text)
    {
        if (!is_string($text)) return false; #FIXME original treats between -128 and 255 inclusive as ASCII chars
        if (preg_match('/^\d+$/', $text)) return true;
        return false;
    }
}

if (!function_exists('gzopen') && function_exists('gzopen64')) {
    /**
     * work around for PHP compiled against certain zlib versions #865
     *
     * @link http://stackoverflow.com/questions/23417519/php-zlib-gzopen-not-exists
     *
     * @param string $filename
     * @param string $mode
     * @param int $use_include_path
     * @return mixed
     */
    function gzopen($filename, $mode, $use_include_path = 0)
    {
        return gzopen64($filename, $mode, $use_include_path);
    }
}

if (!function_exists('gzseek') && function_exists('gzseek64')) {
    /**
     * work around for PHP compiled against certain zlib versions #865
     *
     * @link http://stackoverflow.com/questions/23417519/php-zlib-gzopen-not-exists
     *
     * @param resource $zp
     * @param int $offset
     * @param int $whence
     * @return int
     */
    function gzseek($zp, $offset, $whence = SEEK_SET)
    {
        return gzseek64($zp, $offset, $whence);
    }
}

if (!function_exists('gztell') && function_exists('gztell64')) {
    /**
     * work around for PHP compiled against certain zlib versions #865
     *
     * @link   http://stackoverflow.com/questions/23417519/php-zlib-gzopen-not-exists
     *
     * @param resource $zp
     * @return int
     */
    function gztell($zp)
    {
        return gztell64($zp);
    }
}

/**
 * polyfill for PHP < 8
 * @see https://www.php.net/manual/en/function.str-starts-with
 */
if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle)
    {
        return empty($needle) || strpos($haystack, $needle) === 0;
    }
}

/**
 * polyfill for PHP < 8
 * @see https://www.php.net/manual/en/function.str-contains
 */
if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle)
    {
        return empty($needle) || strpos($haystack, $needle) !== false;
    }
}

/**
 * polyfill for PHP < 8
 * @see https://www.php.net/manual/en/function.str-ends-with
 */
if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle)
    {
        return empty($needle) || substr($haystack, -strlen($needle)) === $needle;
    }
}

/**
 * polyfill for PHP < 8.1
 * @see https://www.php.net/manual/en/function.array-is-list
 */
if (!function_exists('array_is_list')) {
    function array_is_list(array $arr)
    {
        if ($arr === []) {
            return true;
        }
        return array_keys($arr) === range(0, count($arr) - 1);
    }
}
