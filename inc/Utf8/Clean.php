<?php

namespace dokuwiki\Utf8;

/**
 * Methods to assess and clean UTF-8 strings
 */
class Clean
{
    /**
     * Checks if a string contains 7bit ASCII only
     *
     * @author Andreas Haerter <andreas.haerter@dev.mail-node.com>
     *
     * @param string $str
     * @return bool
     */
    public static function isASCII($str)
    {
        return (preg_match('/(?:[^\x00-\x7F])/', $str) !== 1);
    }

    /**
     * Tries to detect if a string is in Unicode encoding
     *
     * @author <bmorel@ssi.fr>
     * @link   http://php.net/manual/en/function.utf8-encode.php
     *
     * @param string $str
     * @return bool
     */
    public static function isUtf8($str)
    {
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $b = ord($str[$i]);
            if ($b < 0x80) continue; # 0bbbbbbb
            elseif (($b & 0xE0) === 0xC0) $n = 1; # 110bbbbb
            elseif (($b & 0xF0) === 0xE0) $n = 2; # 1110bbbb
            elseif (($b & 0xF8) === 0xF0) $n = 3; # 11110bbb
            elseif (($b & 0xFC) === 0xF8) $n = 4; # 111110bb
            elseif (($b & 0xFE) === 0xFC) $n = 5; # 1111110b
            else return false; # Does not match any model

            for ($j = 0; $j < $n; $j++) { # n bytes matching 10bbbbbb follow ?
                if ((++$i === $len) || ((ord($str[$i]) & 0xC0) !== 0x80))
                    return false;
            }
        }
        return true;
    }

    /**
     * Strips all high byte chars
     *
     * Returns a pure ASCII7 string
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * @param string $str
     * @return string
     */
    public static function strip($str)
    {
        $ascii = '';
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            if (ord($str[$i]) < 128) {
                $ascii .= $str[$i];
            }
        }
        return $ascii;
    }

    /**
     * Removes special characters (nonalphanumeric) from a UTF-8 string
     *
     * This function adds the controlchars 0x00 to 0x19 to the array of
     * stripped chars (they are not included in $UTF8_SPECIAL_CHARS)
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * @param  string $string The UTF8 string to strip of special chars
     * @param  string $repl Replace special with this string
     * @param  string $additional Additional chars to strip (used in regexp char class)
     * @return string
     */
    public static function stripspecials($string, $repl = '', $additional = '')
    {
        static $specials = null;
        if ($specials === null) {
            $specials = preg_quote(Table::specialChars(), '/');
        }

        return preg_replace('/[' . $additional . '\x00-\x19' . $specials . ']/u', $repl, $string);
    }

    /**
     * Replace bad bytes with an alternative character
     *
     * ASCII character is recommended for replacement char
     *
     * PCRE Pattern to locate bad bytes in a UTF-8 string
     * Comes from W3 FAQ: Multilingual Forms
     * Note: modified to include full ASCII range including control chars
     *
     * @author Harry Fuecks <hfuecks@gmail.com>
     * @see http://www.w3.org/International/questions/qa-forms-utf-8
     *
     * @param string $str to search
     * @param string $replace to replace bad bytes with (defaults to '?') - use ASCII
     * @return string
     */
    public static function replaceBadBytes($str, $replace = '')
    {
        $UTF8_BAD =
            '([\x00-\x7F]' .                          # ASCII (including control chars)
            '|[\xC2-\xDF][\x80-\xBF]' .               # non-overlong 2-byte
            '|\xE0[\xA0-\xBF][\x80-\xBF]' .           # excluding overlongs
            '|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}' .    # straight 3-byte
            '|\xED[\x80-\x9F][\x80-\xBF]' .           # excluding surrogates
            '|\xF0[\x90-\xBF][\x80-\xBF]{2}' .        # planes 1-3
            '|[\xF1-\xF3][\x80-\xBF]{3}' .            # planes 4-15
            '|\xF4[\x80-\x8F][\x80-\xBF]{2}' .        # plane 16
            '|(.{1}))';                               # invalid byte
        ob_start();
        while (preg_match('/' . $UTF8_BAD . '/S', $str, $matches)) {
            if (!isset($matches[2])) {
                echo $matches[0];
            } else {
                echo $replace;
            }
            $str = substr($str, strlen($matches[0]));
        }
        return ob_get_clean();
    }


    /**
     * Replace accented UTF-8 characters by unaccented ASCII-7 equivalents
     *
     * Use the optional parameter to just deaccent lower ($case = -1) or upper ($case = 1)
     * letters. Default is to deaccent both cases ($case = 0)
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * @param string $string
     * @param int $case
     * @return string
     */
    public static function deaccent($string, $case = 0)
    {
        if ($case <= 0) {
            $string = strtr($string, Table::lowerAccents());
        }
        if ($case >= 0) {
            $string = strtr($string, Table::upperAccents());
        }
        return $string;
    }

    /**
     * Romanize a non-latin string
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * @param string $string
     * @return string
     */
    public static function romanize($string)
    {
        if (self::isASCII($string)) return $string; //nothing to do

        return strtr($string, Table::romanization());
    }

    /**
     * adjust a byte index into a utf8 string to a utf8 character boundary
     *
     * @author       chris smith <chris@jalakai.co.uk>
     *
     * @param string $str utf8 character string
     * @param int $i byte index into $str
     * @param bool $next direction to search for boundary, false = up (current character) true = down (next character)
     * @return int byte index into $str now pointing to a utf8 character boundary
     */
    public static function correctIdx($str, $i, $next = false)
    {

        if ($i <= 0) return 0;

        $limit = strlen($str);
        if ($i >= $limit) return $limit;

        if ($next) {
            while (($i < $limit) && ((ord($str[$i]) & 0xC0) === 0x80)) $i++;
        } else {
            while ($i && ((ord($str[$i]) & 0xC0) === 0x80)) $i--;
        }

        return $i;
    }

}
