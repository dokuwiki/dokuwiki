<?php
/**
 * compatibility functions
 *
 * This file contains a few functions that might be missing from the PHP build
 */

if(!function_exists('ctype_space')) {
    /**
     * Check for whitespace character(s)
     *
     * @see ctype_space
     * @param string $text
     * @return bool
     */
    function ctype_space($text) {
        if(!is_string($text)) return false; #FIXME original treats between -128 and 255 inclusive as ASCII chars
        if(trim($text) === '') return true;
        return false;
    }
}

if(!function_exists('ctype_digit')) {
    /**
     * Check for numeric character(s)
     *
     * @see ctype_digit
     * @param string $text
     * @return bool
     */
    function ctype_digit($text) {
        if(!is_string($text)) return false; #FIXME original treats between -128 and 255 inclusive as ASCII chars
        if(preg_match('/^\d+$/', $text)) return true;
        return false;
    }
}

if(!function_exists('gzopen') && function_exists('gzopen64')) {
    /**
     * work around for PHP compiled against certain zlib versions #865
     *
     * @link http://stackoverflow.com/questions/23417519/php-zlib-gzopen-not-exists
     *
     * @param string $filename
     * @param string $mode
     * @param int    $use_include_path
     * @return mixed
     */
    function gzopen($filename, $mode, $use_include_path = 0) {
        return gzopen64($filename, $mode, $use_include_path);
    }
}

if(!function_exists('gzseek') && function_exists('gzseek64')) {
    /**
     * work around for PHP compiled against certain zlib versions #865
     *
     * @link http://stackoverflow.com/questions/23417519/php-zlib-gzopen-not-exists
     *
     * @param resource $zp
     * @param int      $offset
     * @param int      $whence
     * @return int
     */
    function gzseek($zp, $offset, $whence = SEEK_SET) {
        return gzseek64($zp, $offset, $whence);
    }
}

if(!function_exists('gztell') && function_exists('gztell64')) {
    /**
     * work around for PHP compiled against certain zlib versions #865
     *
     * @link   http://stackoverflow.com/questions/23417519/php-zlib-gzopen-not-exists
     *
     * @param resource $zp
     * @return int
     */
    function gztell($zp) {
        return gztell64($zp);
    }
}

