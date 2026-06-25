<?php

namespace dokuwiki\Parsing\Helpers;

/**
 * Pure helper for applying GFM backslash-escape rules to literal text
 * that didn't pass through the GfmEscape lexer mode.
 *
 * Whole-span PROTECTED modes (GfmCode, GfmLink, …) capture their body
 * in a single regex match, so the inline GfmEscape pattern never gets
 * to the bytes inside. For the slots GFM still wants escaped — fenced
 * code info strings, link destinations, link titles — call this helper
 * after extracting the literal substring.
 */
class Escape
{
    /**
     * Regex character class matching every GFM §6.1 escapable ASCII
     * punctuation char. Shared by GfmEscape's lexer pattern and
     * unescapeBackslashes() so the two stay in lockstep.
     *
     * The encoding looks busy because of nested PHP-string + PCRE
     * escaping: the embedded `\\\\\]` produces the regex `\\\]`,
     * i.e. a literal `\` and a literal `]` inside the char class.
     */
    public const PUNCTUATION_CHAR_CLASS = '[!"#$%&\'()*+,\-./:;<=>?@\[\\\\\]^_`{|}~]';

    /**
     * Replace each `\X` (where X is GFM-escapable ASCII punctuation)
     * with the literal X.
     */
    public static function unescapeBackslashes(string $text): string
    {
        // Paired `{...}` delimiters: PHP single-char delimiters (`/`, `~`,
        // `#`) appearing inside the regex terminate it early. Every char
        // we'd want as a delimiter is in our escapable class, so we use
        // the paired form — PCRE treats `}` as the closer only at the
        // outermost level, not inside the `[...]` class.
        return preg_replace(
            '{\\\\(' . self::PUNCTUATION_CHAR_CLASS . ')}',
            '$1',
            $text
        );
    }
}
