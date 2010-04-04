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

    private static $plain = '/_-0123456789abcdefghijklmnopqrstuvwxyz'; // these characters aren't converted
    private static $pre_indicator = '%';
    private static $post_indicator = '.';                             // this character can be included in "plain" set
    private static $adjustments = array();                            // must be initialized, use getAdjustments()

    /**
     * Convert an UTF-8 string to a safe ASCII String
     *
     *  conversion process
     *    - if codepoint is a plain character,
     *      - if previous character was "converted", append post_indicator
     *        to output
     *      - append ascii byte for character to output (continue to
     *        next character)
     *
     *    - reduce codepoint value to fill the holes left by "plain"
     *    - choose marker character for conversion by taking modulus
     *      (number of possible pre_indicators) of modified codepoint
     *    - calculate value for conversion to base36 by integer division
     *      (number of possible pre_indicators) of modified codepoint
     *    - convert above value to a base36 string
     *    - append marker characater followed by base36 string to
     *      output (continue to next character)
     */
    public function encode($utf8) {
        return self::unicode_safe(self::utf8_unicode($utf8));
    }

    /**
     *  decoding process
     *    - split the string into substrings at marker characters,
     *      discarding post_indicator character but keeping
     *      pre_indicator characters (along with their following
     *      base36 string)
     *    - check the first character of the substring
     *      - if its not a pre_indicator character, convert each
     *        character in the substring into its codepoint value
     *        and append to output (continue to next substring)
     *      - if it is a pre_indicator character, get its position in the
     *        pre_indicator string (order is important)
     *    - convert the remainder of the string from base36 to base10
     *      and then to an (int).
     *    - multiply the converted int by the number of pre_indicator
     *      characters and add the pre_indicator position
     *    - reverse the conversion adjustment for codepoint holes left by
     *      "plain" characters
     *    - append resulting codepoint value to output (continue to next
     *      substring)
     */
    public function decode($safe) {
        return self::unicode_utf8(self::safe_unicode(strtolower($safe)));
    }

    public function validate_printable_utf8($printable_utf8) {
        return !preg_match('/[\x01-\x1f]/',$printable_utf8);
    }

    public function validate_safe($safe) {
        return !preg_match('/[^'.self::$plain.self::$post_indicator.self::$pre_indicator.']/',$safe);
    }

    private function utf8_unicode($utf8) {
        return utf8_to_unicode($utf8);
    }

    private function unicode_utf8($unicode) {
        return unicode_to_utf8($unicode);
    }

    private function unicode_safe($unicode) {

        $safe = '';
        $converted = false;

        foreach ($unicode as $codepoint) {
            if (self::isPlain($codepoint)) {
                if ($converted) {
                    $safe .= self::$post_indicator;
                    $converted = false;
                }
                $safe .= chr($codepoint);

            } else if (self::isPreIndicator($codepoint)) {
                $converted = true;
                $safe .= chr($codepoint);

            } else {
                $converted = true;
                $adjusted = self::adjustForPlain($codepoint);

                $marker = $adjusted % strlen(self::$pre_indicator);
                $base = (int) ($adjusted / strlen(self::$pre_indicator));

                $safe .= self::$pre_indicator[$marker];
                $safe .= base_convert((string)$base,10,36);
            }
        }
        return $safe;
    }

    private function safe_unicode($safe) {
        $unicode = array();
        $split = preg_split('/(?=['.self::$post_indicator.self::$pre_indicator.'])/',$safe,-1,PREG_SPLIT_NO_EMPTY);

        $converted = false;
        foreach ($split as $sub) {
            if (($marker = strpos(self::$pre_indicator,$sub[0])) === false) {
                if ($converted) {
                    // strip post_indicator
                    $sub = substr($sub,1);
                    $converted = false;
                }
                for ($i=0; $i < strlen($sub); $i++) {
                    $unicode[] = ord($sub[$i]);
                }
            } else if (strlen($sub)==1) {
                $converted =  true;
                $unicode[] = ord($sub);
            } else {
                // a single codepoint in our base
                $converted = true;
                $base = (int)base_convert(substr($sub,1),36,10);
                $adjusted = ($base*strlen(self::$pre_indicator)) + $marker;

                $unicode[] = self::reverseForPlain($adjusted);
            }
        }

        return $unicode;
    }

    private function isPlain($codepoint) {
        return ($codepoint < 127 && (strpos(self::$plain.self::$post_indicator,chr($codepoint))!==false));
    }

    private function isPreIndicator($codepoint) {
        return ($codepoint < 127 && (strpos(self::$pre_indicator,chr($codepoint)) !== false));
    }

    /**
     * adjust for plain and non-printable (ascii 0-31)
     * this makes SPACE (0x20) the first character we allow
     */
    private function adjustForPlain($codepoint) {
        $adjustment = self::getAdjustments();

        // codepoint is higher than that of the plain character with the highest codepoint
        if ($codepoint > ord($adjustment[count($adjustment)-1])) {
            $adjusted = $codepoint - count($adjustment);
        } else if ($codepoint > ord($adjustment[0])) {
            for ($i=1; $i < count($adjustment); $i++) {
                if ($codepoint < ord($adjustment[$i])) {
                    break;
                }
            }
            $adjusted = $codepoint - $i;
        } else {
            $adjusted = $codepoint;
        }

        // substract number of non-printable characters and return
        return $adjusted - ord(' ');
    }

    private function reverseForPlain($adjusted) {
        $adjustment = self::getAdjustments();

        // reverse adjustment for non-printable characters
        $adjusted += ord(' ');

        if ($adjusted + count($adjustment) > ord($adjustment[count($adjustment)-1])) {
            $adjusted += count($adjustment);
        } else if ($adjusted > ord($adjustment[0])) {
            for ($i=1; $i < count($adjustment); $i++) {
                if ($adjusted + $i < ord($adjustment[$i])) {
                    break;
                }
            }
            $adjusted += $i;
        }

        return $adjusted;
    }

    private function getAdjustments() {
        if (empty(self::$adjustments)) {
            self::$adjustments = str_split(self::$plain.self::$pre_indicator.self::$post_indicator);
            sort(self::$adjustments);
        }

        return self::$adjustments;
    }
}
