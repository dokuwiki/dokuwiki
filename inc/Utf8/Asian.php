<?php

namespace dokuwiki\Utf8;

/**
 * Methods and constants to handle Asian "words"
 *
 * This uses a crude regexp to determine which parts of an Asian string should be treated as words.
 * This is necessary because in some Asian languages a single unicode char represents a whole idea
 * without spaces separating them.
 */
class Asian
{

    /**
     * This defines a non-capturing group for the use in regular expressions to match any asian character that
     * needs to be treated as a word. Uses the Unicode-Ranges for Asian characters taken from
     * http://en.wikipedia.org/wiki/Unicode_block
     */
    const REGEXP =
        '(?:' .

        '[\x{0E00}-\x{0E7F}]' . // Thai

        '|' .

        '[' .
        '\x{2E80}-\x{3040}' .  // CJK -> Hangul
        '\x{309D}-\x{30A0}' .
        '\x{30FD}-\x{31EF}\x{3200}-\x{D7AF}' .
        '\x{F900}-\x{FAFF}' .  // CJK Compatibility Ideographs
        '\x{FE30}-\x{FE4F}' .  // CJK Compatibility Forms
        "\xF0\xA0\x80\x80-\xF0\xAA\x9B\x9F" . // CJK Extension B
        "\xF0\xAA\x9C\x80-\xF0\xAB\x9C\xBF" . // CJK Extension C
        "\xF0\xAB\x9D\x80-\xF0\xAB\xA0\x9F" . // CJK Extension D
        "\xF0\xAF\xA0\x80-\xF0\xAF\xAB\xBF" . // CJK Compatibility Supplement
        ']' .

        '|' .

        '[' .                // Hiragana/Katakana (can be two characters)
        '\x{3042}\x{3044}\x{3046}\x{3048}' .
        '\x{304A}-\x{3062}\x{3064}-\x{3082}' .
        '\x{3084}\x{3086}\x{3088}-\x{308D}' .
        '\x{308F}-\x{3094}' .
        '\x{30A2}\x{30A4}\x{30A6}\x{30A8}' .
        '\x{30AA}-\x{30C2}\x{30C4}-\x{30E2}' .
        '\x{30E4}\x{30E6}\x{30E8}-\x{30ED}' .
        '\x{30EF}-\x{30F4}\x{30F7}-\x{30FA}' .
        '][' .
        '\x{3041}\x{3043}\x{3045}\x{3047}\x{3049}' .
        '\x{3063}\x{3083}\x{3085}\x{3087}\x{308E}\x{3095}-\x{309C}' .
        '\x{30A1}\x{30A3}\x{30A5}\x{30A7}\x{30A9}' .
        '\x{30C3}\x{30E3}\x{30E5}\x{30E7}\x{30EE}\x{30F5}\x{30F6}\x{30FB}\x{30FC}' .
        '\x{31F0}-\x{31FF}' .
        ']?' .
        ')';


    /**
     * Check if the given term contains Asian word characters
     *
     * @param string $term
     * @return bool
     */
    public static function isAsianWords($term)
    {
        return (bool)preg_match('/' . self::REGEXP . '/u', $term);
    }

    /**
     * Surround all Asian words in the given text with the given separator
     *
     * @param string $text Original text containing asian words
     * @param string $sep the separator to use
     * @return string Text with separated asian words
     */
    public static function separateAsianWords($text, $sep = ' ')
    {
        // handle asian chars as single words (may fail on older PHP version)
        $asia = @preg_replace('/(' . self::REGEXP . ')/u', $sep . '\1' . $sep, $text);
        if (!is_null($asia)) $text = $asia; // recover from regexp falure

        return $text;
    }

    /**
     * Split the given text into separate parts
     *
     * Each part is either a non-asian string, or a single asian word
     *
     * @param string $term
     * @return string[]
     */
    public static function splitAsianWords($term)
    {
        return preg_split('/(' . self::REGEXP . '+)/u', $term, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    }
}
