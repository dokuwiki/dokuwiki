<?php

namespace dokuwiki\Utf8;

/**
 * UTF-8 aware equivalents to PHP's string functions
 */
class PhpString
{

    /**
     * A locale independent basename() implementation
     *
     * works around a bug in PHP's basename() implementation
     *
     * @param string $path A path
     * @param string $suffix If the name component ends in suffix this will also be cut off
     * @return string
     * @link   https://bugs.php.net/bug.php?id=37738
     *
     * @see basename()
     */
    public static function basename($path, $suffix = '')
    {
        $path = trim($path, '\\/');
        $rpos = max(strrpos($path, '/'), strrpos($path, '\\'));
        if ($rpos) {
            $path = substr($path, $rpos + 1);
        }

        $suflen = strlen($suffix);
        if ($suflen && (substr($path, -$suflen) === $suffix)) {
            $path = substr($path, 0, -$suflen);
        }

        return $path;
    }

    /**
     * Unicode aware replacement for strlen()
     *
     * utf8_decode() converts characters that are not in ISO-8859-1
     * to '?', which, for the purpose of counting, is alright - It's
     * even faster than mb_strlen.
     *
     * @param string $string
     * @return int
     * @see    utf8_decode()
     *
     * @author <chernyshevsky at hotmail dot com>
     * @see    strlen()
     */
    public static function strlen($string)
    {
        if (function_exists('utf8_decode')) {
            return strlen(utf8_decode($string));
        }

        if (UTF8_MBSTRING) {
            return mb_strlen($string, 'UTF-8');
        }

        if (function_exists('iconv_strlen')) {
            return iconv_strlen($string, 'UTF-8');
        }

        return strlen($string);
    }

    /**
     * UTF-8 aware alternative to substr
     *
     * Return part of a string given character offset (and optionally length)
     *
     * @param string $str
     * @param int $offset number of UTF-8 characters offset (from left)
     * @param int $length (optional) length in UTF-8 characters from offset
     * @return string
     * @author Harry Fuecks <hfuecks@gmail.com>
     * @author Chris Smith <chris@jalakai.co.uk>
     *
     */
    public static function substr($str, $offset, $length = null)
    {
        if (UTF8_MBSTRING) {
            if ($length === null) {
                return mb_substr($str, $offset);
            }

            return mb_substr($str, $offset, $length);
        }

        /*
         * Notes:
         *
         * no mb string support, so we'll use pcre regex's with 'u' flag
         * pcre only supports repetitions of less than 65536, in order to accept up to MAXINT values for
         * offset and length, we'll repeat a group of 65535 characters when needed (ok, up to MAXINT-65536)
         *
         * substr documentation states false can be returned in some cases (e.g. offset > string length)
         * mb_substr never returns false, it will return an empty string instead.
         *
         * calculating the number of characters in the string is a relatively expensive operation, so
         * we only carry it out when necessary. It isn't necessary for +ve offsets and no specified length
         */

        // cast parameters to appropriate types to avoid multiple notices/warnings
        $str = (string)$str;                          // generates E_NOTICE for PHP4 objects, but not PHP5 objects
        $offset = (int)$offset;
        if ($length !== null) $length = (int)$length;

        // handle trivial cases
        if ($length === 0) return '';
        if ($offset < 0 && $length < 0 && $length < $offset) return '';

        $offset_pattern = '';
        $length_pattern = '';

        // normalise -ve offsets (we could use a tail anchored pattern, but they are horribly slow!)
        if ($offset < 0) {
            $strlen = self::strlen($str);        // see notes
            $offset = $strlen + $offset;
            if ($offset < 0) $offset = 0;
        }

        // establish a pattern for offset, a non-captured group equal in length to offset
        if ($offset > 0) {
            $Ox = (int)($offset / 65535);
            $Oy = $offset % 65535;

            if ($Ox) $offset_pattern = '(?:.{65535}){' . $Ox . '}';
            $offset_pattern = '^(?:' . $offset_pattern . '.{' . $Oy . '})';
        } else {
            $offset_pattern = '^';                      // offset == 0; just anchor the pattern
        }

        // establish a pattern for length
        if ($length === null) {
            $length_pattern = '(.*)$';                  // the rest of the string
        } else {

            if (!isset($strlen)) $strlen = self::strlen($str);    // see notes
            if ($offset > $strlen) return '';           // another trivial case

            if ($length > 0) {

                // reduce any length that would go past the end of the string
                $length = min($strlen - $offset, $length);

                $Lx = (int)($length / 65535);
                $Ly = $length % 65535;

                // +ve length requires ... a captured group of length characters
                if ($Lx) $length_pattern = '(?:.{65535}){' . $Lx . '}';
                $length_pattern = '(' . $length_pattern . '.{' . $Ly . '})';

            } else if ($length < 0) {

                if ($length < ($offset - $strlen)) return '';

                $Lx = (int)((-$length) / 65535);
                $Ly = (-$length) % 65535;

                // -ve length requires ... capture everything except a group of -length characters
                //                         anchored at the tail-end of the string
                if ($Lx) $length_pattern = '(?:.{65535}){' . $Lx . '}';
                $length_pattern = '(.*)(?:' . $length_pattern . '.{' . $Ly . '})$';
            }
        }

        if (!preg_match('#' . $offset_pattern . $length_pattern . '#us', $str, $match)) return '';
        return $match[1];
    }

    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    /**
     * Unicode aware replacement for substr_replace()
     *
     * @param string $string input string
     * @param string $replacement the replacement
     * @param int $start the replacing will begin at the start'th offset into string.
     * @param int $length If given and is positive, it represents the length of the portion of string which is
     *                            to be replaced. If length is zero then this function will have the effect of inserting
     *                            replacement into string at the given start offset.
     * @return string
     * @see    substr_replace()
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    public static function substr_replace($string, $replacement, $start, $length = 0)
    {
        $ret = '';
        if ($start > 0) $ret .= self::substr($string, 0, $start);
        $ret .= $replacement;
        $ret .= self::substr($string, $start + $length);
        return $ret;
    }
    // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

    /**
     * Unicode aware replacement for ltrim()
     *
     * @param string $str
     * @param string $charlist
     * @return string
     * @see    ltrim()
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    public static function ltrim($str, $charlist = '')
    {
        if ($charlist === '') return ltrim($str);

        //quote charlist for use in a characterclass
        $charlist = preg_replace('!([\\\\\\-\\]\\[/])!', '\\\${1}', $charlist);

        return preg_replace('/^[' . $charlist . ']+/u', '', $str);
    }

    /**
     * Unicode aware replacement for rtrim()
     *
     * @param string $str
     * @param string $charlist
     * @return string
     * @see    rtrim()
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    public static function rtrim($str, $charlist = '')
    {
        if ($charlist === '') return rtrim($str);

        //quote charlist for use in a characterclass
        $charlist = preg_replace('!([\\\\\\-\\]\\[/])!', '\\\${1}', $charlist);

        return preg_replace('/[' . $charlist . ']+$/u', '', $str);
    }

    /**
     * Unicode aware replacement for trim()
     *
     * @param string $str
     * @param string $charlist
     * @return string
     * @see    trim()
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    public static function trim($str, $charlist = '')
    {
        if ($charlist === '') return trim($str);

        return self::ltrim(self::rtrim($str, $charlist), $charlist);
    }

    /**
     * This is a unicode aware replacement for strtolower()
     *
     * Uses mb_string extension if available
     *
     * @param string $string
     * @return string
     * @see    \dokuwiki\Utf8\PhpString::strtoupper()
     *
     * @author Leo Feyer <leo@typolight.org>
     * @see    strtolower()
     */
    public static function strtolower($string)
    {
        if (UTF8_MBSTRING) {
            if (class_exists('Normalizer', $autoload = false)) {
                return \Normalizer::normalize(mb_strtolower($string, 'utf-8'));
            }
            return (mb_strtolower($string, 'utf-8'));
        }
        return strtr($string, Table::upperCaseToLowerCase());
    }

    /**
     * This is a unicode aware replacement for strtoupper()
     *
     * Uses mb_string extension if available
     *
     * @param string $string
     * @return string
     * @see    \dokuwiki\Utf8\PhpString::strtoupper()
     *
     * @author Leo Feyer <leo@typolight.org>
     * @see    strtoupper()
     */
    public static function strtoupper($string)
    {
        if (UTF8_MBSTRING) return mb_strtoupper($string, 'utf-8');

        return strtr($string, Table::lowerCaseToUpperCase());
    }


    /**
     * UTF-8 aware alternative to ucfirst
     * Make a string's first character uppercase
     *
     * @param string $str
     * @return string with first character as upper case (if applicable)
     * @author Harry Fuecks
     *
     */
    public static function ucfirst($str)
    {
        switch (self::strlen($str)) {
            case 0:
                return '';
            case 1:
                return self::strtoupper($str);
            default:
                preg_match('/^(.{1})(.*)$/us', $str, $matches);
                return self::strtoupper($matches[1]) . $matches[2];
        }
    }

    /**
     * UTF-8 aware alternative to ucwords
     * Uppercase the first character of each word in a string
     *
     * @param string $str
     * @return string with first char of each word uppercase
     * @author Harry Fuecks
     * @see http://php.net/ucwords
     *
     */
    public static function ucwords($str)
    {
        // Note: [\x0c\x09\x0b\x0a\x0d\x20] matches;
        // form feeds, horizontal tabs, vertical tabs, linefeeds and carriage returns
        // This corresponds to the definition of a "word" defined at http://php.net/ucwords
        $pattern = '/(^|([\x0c\x09\x0b\x0a\x0d\x20]+))([^\x0c\x09\x0b\x0a\x0d\x20]{1})[^\x0c\x09\x0b\x0a\x0d\x20]*/u';

        return preg_replace_callback(
            $pattern,
            function ($matches) {
                $leadingws = $matches[2];
                $ucfirst = self::strtoupper($matches[3]);
                $ucword = self::substr_replace(ltrim($matches[0]), $ucfirst, 0, 1);
                return $leadingws . $ucword;
            },
            $str
        );
    }

    /**
     * This is an Unicode aware replacement for strpos
     *
     * @param string $haystack
     * @param string $needle
     * @param integer $offset
     * @return integer
     * @author Leo Feyer <leo@typolight.org>
     * @see    strpos()
     *
     */
    public static function strpos($haystack, $needle, $offset = 0)
    {
        $comp = 0;
        $length = null;

        while ($length === null || $length < $offset) {
            $pos = strpos($haystack, $needle, $offset + $comp);

            if ($pos === false)
                return false;

            $length = self::strlen(substr($haystack, 0, $pos));

            if ($length < $offset)
                $comp = $pos - $length;
        }

        return $length;
    }


}
