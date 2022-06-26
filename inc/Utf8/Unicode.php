<?php

namespace dokuwiki\Utf8;

/**
 * Convert between UTF-8 and a list of Unicode Code Points
 */
class Unicode
{

    /**
     * Takes an UTF-8 string and returns an array of ints representing the
     * Unicode characters. Astral planes are supported ie. the ints in the
     * output can be > 0xFFFF. Occurrances of the BOM are ignored. Surrogates
     * are not allowed.
     *
     * If $strict is set to true the function returns false if the input
     * string isn't a valid UTF-8 octet sequence and raises a PHP error at
     * level E_USER_WARNING
     *
     * Note: this function has been modified slightly in this library to
     * trigger errors on encountering bad bytes
     *
     * @author <hsivonen@iki.fi>
     * @author Harry Fuecks <hfuecks@gmail.com>
     * @see    unicode_to_utf8
     * @link   http://hsivonen.iki.fi/php-utf8/
     * @link   http://sourceforge.net/projects/phputf8/
     * @todo break into less complex chunks
     * @todo use exceptions instead of user errors
     *
     * @param  string $str UTF-8 encoded string
     * @param  boolean $strict Check for invalid sequences?
     * @return mixed array of unicode code points or false if UTF-8 invalid
     */
    public static function fromUtf8($str, $strict = false)
    {
        $mState = 0;     // cached expected number of octets after the current octet
        // until the beginning of the next UTF8 character sequence
        $mUcs4 = 0;     // cached Unicode character
        $mBytes = 1;     // cached expected number of octets in the current sequence

        $out = array();

        $len = strlen($str);

        for ($i = 0; $i < $len; $i++) {

            $in = ord($str[$i]);

            if ($mState === 0) {

                // When mState is zero we expect either a US-ASCII character or a
                // multi-octet sequence.
                if (0 === (0x80 & $in)) {
                    // US-ASCII, pass straight through.
                    $out[] = $in;
                    $mBytes = 1;

                } else if (0xC0 === (0xE0 & $in)) {
                    // First octet of 2 octet sequence
                    $mUcs4 = $in;
                    $mUcs4 = ($mUcs4 & 0x1F) << 6;
                    $mState = 1;
                    $mBytes = 2;

                } else if (0xE0 === (0xF0 & $in)) {
                    // First octet of 3 octet sequence
                    $mUcs4 = $in;
                    $mUcs4 = ($mUcs4 & 0x0F) << 12;
                    $mState = 2;
                    $mBytes = 3;

                } else if (0xF0 === (0xF8 & $in)) {
                    // First octet of 4 octet sequence
                    $mUcs4 = $in;
                    $mUcs4 = ($mUcs4 & 0x07) << 18;
                    $mState = 3;
                    $mBytes = 4;

                } else if (0xF8 === (0xFC & $in)) {
                    /* First octet of 5 octet sequence.
                     *
                     * This is illegal because the encoded codepoint must be either
                     * (a) not the shortest form or
                     * (b) outside the Unicode range of 0-0x10FFFF.
                     * Rather than trying to resynchronize, we will carry on until the end
                     * of the sequence and let the later error handling code catch it.
                     */
                    $mUcs4 = $in;
                    $mUcs4 = ($mUcs4 & 0x03) << 24;
                    $mState = 4;
                    $mBytes = 5;

                } else if (0xFC === (0xFE & $in)) {
                    // First octet of 6 octet sequence, see comments for 5 octet sequence.
                    $mUcs4 = $in;
                    $mUcs4 = ($mUcs4 & 1) << 30;
                    $mState = 5;
                    $mBytes = 6;

                } elseif ($strict) {
                    /* Current octet is neither in the US-ASCII range nor a legal first
                     * octet of a multi-octet sequence.
                     */
                    trigger_error(
                        'utf8_to_unicode: Illegal sequence identifier ' .
                        'in UTF-8 at byte ' . $i,
                        E_USER_WARNING
                    );
                    return false;

                }

            } else {

                // When mState is non-zero, we expect a continuation of the multi-octet
                // sequence
                if (0x80 === (0xC0 & $in)) {

                    // Legal continuation.
                    $shift = ($mState - 1) * 6;
                    $tmp = $in;
                    $tmp = ($tmp & 0x0000003F) << $shift;
                    $mUcs4 |= $tmp;

                    /**
                     * End of the multi-octet sequence. mUcs4 now contains the final
                     * Unicode codepoint to be output
                     */
                    if (0 === --$mState) {

                        /*
                         * Check for illegal sequences and codepoints.
                         */
                        // From Unicode 3.1, non-shortest form is illegal
                        if (((2 === $mBytes) && ($mUcs4 < 0x0080)) ||
                            ((3 === $mBytes) && ($mUcs4 < 0x0800)) ||
                            ((4 === $mBytes) && ($mUcs4 < 0x10000)) ||
                            (4 < $mBytes) ||
                            // From Unicode 3.2, surrogate characters are illegal
                            (($mUcs4 & 0xFFFFF800) === 0xD800) ||
                            // Codepoints outside the Unicode range are illegal
                            ($mUcs4 > 0x10FFFF)) {

                            if ($strict) {
                                trigger_error(
                                    'utf8_to_unicode: Illegal sequence or codepoint ' .
                                    'in UTF-8 at byte ' . $i,
                                    E_USER_WARNING
                                );

                                return false;
                            }

                        }

                        if (0xFEFF !== $mUcs4) {
                            // BOM is legal but we don't want to output it
                            $out[] = $mUcs4;
                        }

                        //initialize UTF8 cache
                        $mState = 0;
                        $mUcs4 = 0;
                        $mBytes = 1;
                    }

                } elseif ($strict) {
                    /**
                     *((0xC0 & (*in) != 0x80) && (mState != 0))
                     * Incomplete multi-octet sequence.
                     */
                    trigger_error(
                        'utf8_to_unicode: Incomplete multi-octet ' .
                        '   sequence in UTF-8 at byte ' . $i,
                        E_USER_WARNING
                    );

                    return false;
                }
            }
        }
        return $out;
    }

    /**
     * Takes an array of ints representing the Unicode characters and returns
     * a UTF-8 string. Astral planes are supported ie. the ints in the
     * input can be > 0xFFFF. Occurrances of the BOM are ignored. Surrogates
     * are not allowed.
     *
     * If $strict is set to true the function returns false if the input
     * array contains ints that represent surrogates or are outside the
     * Unicode range and raises a PHP error at level E_USER_WARNING
     *
     * Note: this function has been modified slightly in this library to use
     * output buffering to concatenate the UTF-8 string (faster) as well as
     * reference the array by it's keys
     *
     * @param  array $arr of unicode code points representing a string
     * @param  boolean $strict Check for invalid sequences?
     * @return string|false UTF-8 string or false if array contains invalid code points
     *
     * @author <hsivonen@iki.fi>
     * @author Harry Fuecks <hfuecks@gmail.com>
     * @see    utf8_to_unicode
     * @link   http://hsivonen.iki.fi/php-utf8/
     * @link   http://sourceforge.net/projects/phputf8/
     * @todo use exceptions instead of user errors
     */
    public static function toUtf8($arr, $strict = false)
    {
        if (!is_array($arr)) return '';
        ob_start();

        foreach (array_keys($arr) as $k) {

            if (($arr[$k] >= 0) && ($arr[$k] <= 0x007f)) {
                # ASCII range (including control chars)

                echo chr($arr[$k]);

            } else if ($arr[$k] <= 0x07ff) {
                # 2 byte sequence

                echo chr(0xc0 | ($arr[$k] >> 6));
                echo chr(0x80 | ($arr[$k] & 0x003f));

            } else if ($arr[$k] == 0xFEFF) {
                # Byte order mark (skip)
                // nop -- zap the BOM

            } else if ($arr[$k] >= 0xD800 && $arr[$k] <= 0xDFFF) {
                # Test for illegal surrogates

                // found a surrogate
                if ($strict) {
                    trigger_error(
                        'unicode_to_utf8: Illegal surrogate ' .
                        'at index: ' . $k . ', value: ' . $arr[$k],
                        E_USER_WARNING
                    );
                    return false;
                }

            } else if ($arr[$k] <= 0xffff) {
                # 3 byte sequence

                echo chr(0xe0 | ($arr[$k] >> 12));
                echo chr(0x80 | (($arr[$k] >> 6) & 0x003f));
                echo chr(0x80 | ($arr[$k] & 0x003f));

            } else if ($arr[$k] <= 0x10ffff) {
                # 4 byte sequence

                echo chr(0xf0 | ($arr[$k] >> 18));
                echo chr(0x80 | (($arr[$k] >> 12) & 0x3f));
                echo chr(0x80 | (($arr[$k] >> 6) & 0x3f));
                echo chr(0x80 | ($arr[$k] & 0x3f));

            } elseif ($strict) {

                trigger_error(
                    'unicode_to_utf8: Codepoint out of Unicode range ' .
                    'at index: ' . $k . ', value: ' . $arr[$k],
                    E_USER_WARNING
                );

                // out of range
                return false;
            }
        }

        return ob_get_clean();
    }
}
