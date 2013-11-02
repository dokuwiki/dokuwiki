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