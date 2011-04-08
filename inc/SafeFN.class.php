<?php

/**
 *  Class to safely store UTF-8 in a Filename
 *
 *  Encodes a utf8 string using only the following characters 0-9a-z_.-%
 *  characters 0-9a-z in the original string are preserved, "plain".
 *  all other characters are represented in a substring that starts
 *  with '%' are "converted".
 *  The transition from converted substrings to plain characters is
 *  marked with a '.'
 *
 *  @author   Christopher Smith
 *  @date     2010-04-02
 */
class SafeFN {

    // 'safe' characters are a superset of $plain, $pre_indicator and $post_indicator
    private static $plain = '-./[_0123456789abcdefghijklmnopqrstuvwxyz'; // these characters aren't converted
    private static $pre_indicator = '%';
    private static $post_indicator = ']';

    /**
     * Convert an UTF-8 string to a safe ASCII String
     *
     *  conversion process
     *    - if codepoint is a plain or post_indicator character,
     *      - if previous character was "converted", append post_indicator to output, clear "converted" flag
     *      - append ascii byte for character to output
     *      (continue to next character)
     *
     *    - if codepoint is a pre_indicator character,
     *      - append ascii byte for character to output, set "converted" flag
     *      (continue to next character)
     *
     *    (all remaining characters)
     *    - reduce codepoint value for non-printable ASCII characters (0x00 - 0x1f).  Space becomes our zero.
     *    - convert reduced value to base36 (0-9a-z)
     *    - append $pre_indicator characater followed by base36 string to output, set converted flag
     *    (continue to next character)
     *
     * @param    string    $filename     a utf8 string, should only include printable characters - not 0x00-0x1f
     * @return   string    an encoded representation of $filename using only 'safe' ASCII characters
     *
     * @author   Christopher Smith <chris@jalakai.co.uk>
     */
    public function encode($filename) {
        return self::unicode_to_safe(utf8_to_unicode($filename));
    }

    /**
     *  decoding process
     *    - split the string into substrings at any occurrence of pre or post indicator characters
     *    - check the first character of the substring
     *      - if its not a pre_indicator character
     *        - if previous character was converted, skip over post_indicator character
     *        - copy codepoint values of remaining characters to the output array
     *        - clear any converted flag
     *      (continue to next substring)
     *
     *     _ else (its a pre_indicator character)
     *       - if string length is 1, copy the post_indicator character to the output array
     *       (continue to next substring)
     *
     *       - else (string length > 1)
     *         - skip the pre-indicator character and convert remaining string from base36 to base10
     *         - increase codepoint value for non-printable ASCII characters (add 0x20)
     *         - append codepoint to output array
     *       (continue to next substring)
     *
     * @param    string    $filename     a 'safe' encoded ASCII string,
     * @return   string    decoded utf8 representation of $filename
     *
     * @author   Christopher Smith <chris@jalakai.co.uk>
     */
    public function decode($filename) {
        return unicode_to_utf8(self::safe_to_unicode(strtolower($filename)));
    }

    public function validate_printable_utf8($printable_utf8) {
        return !preg_match('#[\x01-\x1f]#',$printable_utf8);
    }

    public function validate_safe($safe) {
        return !preg_match('#[^'.self::$plain.self::$post_indicator.self::$pre_indicator.']#',$safe);
    }

    /**
     * convert an array of unicode codepoints into 'safe_filename' format
     *
     * @param    array  int    $unicode    an array of unicode codepoints
     * @return   string        the unicode represented in 'safe_filename' format
     *
     * @author   Christopher Smith <chris@jalakai.co.uk>
     */
    private function unicode_to_safe($unicode) {

        $safe = '';
        $converted = false;

        foreach ($unicode as $codepoint) {
            if ($codepoint < 127 && (strpos(self::$plain.self::$post_indicator,chr($codepoint))!==false)) {
                if ($converted) {
                    $safe .= self::$post_indicator;
                    $converted = false;
                }
                $safe .= chr($codepoint);

            } else if ($codepoint == ord(self::$pre_indicator)) {
                $safe .= self::$pre_indicator;
                $converted = true;
            } else {
                $safe .= self::$pre_indicator.base_convert((string)($codepoint-32),10,36);
                $converted = true;
            }
        }
        if($converted) $safe .= self::$post_indicator;
        return $safe;
    }

    /**
     * convert a 'safe_filename' string into an array of unicode codepoints
     *
     * @param   string         $safe     a filename in 'safe_filename' format
     * @return  array   int    an array of unicode codepoints
     *
     * @author   Christopher Smith <chris@jalakai.co.uk>
     */
    private function safe_to_unicode($safe) {

        $unicode = array();
        $split = preg_split('#(?=['.self::$post_indicator.self::$pre_indicator.'])#',$safe,-1,PREG_SPLIT_NO_EMPTY);

        $converted = false;
        foreach ($split as $sub) {
            if ($sub[0] != self::$pre_indicator) {
                // plain (unconverted) characters, optionally starting with a post_indicator
                // set initial value to skip any post_indicator
                for ($i=($converted?1:0); $i < strlen($sub); $i++) {
                    $unicode[] = ord($sub[$i]);
                }
                $converted = false;
            } else if (strlen($sub)==1) {
                // a pre_indicator character in the real data
                $unicode[] = ord($sub);
                $converted = true;
            } else {
                // a single codepoint in base36, adjusted for initial 32 non-printable chars
                $unicode[] = 32 + (int)base_convert(substr($sub,1),36,10);
                $converted = true;
            }
        }

        return $unicode;
    }

}
