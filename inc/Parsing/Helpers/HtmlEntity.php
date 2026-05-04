<?php

namespace dokuwiki\Parsing\Helpers;

use dokuwiki\Utf8\Unicode;

/**
 * Pure helper for decoding HTML entity references - numeric (`&#nnn;`,
 * `&#xhhh;`) and HTML5 named (`&copy;`, `&AElig;`, ...) - to their
 * Unicode codepoint(s).
 *
 * Whole-span PROTECTED modes (GfmCode, GfmLink, ...) capture their body
 * in a single regex match, so the inline GfmHtmlEntity pattern never
 * sees the bytes inside. For the slots GFM still wants entity-decoded -
 * fenced code info strings, link destinations, link titles - call
 * decode() after extracting the literal substring.
 *
 * Per CommonMark, codepoint 0, codepoints above U+10FFFF, and the
 * surrogate range U+D800..U+DFFF map to U+FFFD (REPLACEMENT CHARACTER)
 * for numeric references. Unknown named references are returned
 * unchanged - the caller emits them literally and the renderer's
 * &-escaping turns them back into `&amp;xxx;` on output.
 */
class HtmlEntity
{
    /**
     * Regex matching one HTML entity reference. Shared by GfmHtmlEntity
     * (as the lexer special-pattern) and decode() (as the scan
     * pattern), so the two stay in lockstep.
     */
    public const PATTERN = '&(?:#(?:[0-9]{1,7}|[xX][0-9a-fA-F]{1,6})|[a-zA-Z][a-zA-Z0-9]{0,30});';

    protected const REPLACEMENT = "\u{FFFD}";

    /**
     * Decode every HTML entity reference in the given text to its
     * corresponding Unicode codepoint(s). Non-entity bytes pass through
     * unchanged.
     *
     * @param string $text Source text that may contain entity references
     * @return string Text with all recognised entities decoded
     */
    public static function decode(string $text): string
    {
        return preg_replace_callback(
            '/' . self::PATTERN . '/',
            static fn($m) => self::decodeOne($m[0]),
            $text
        );
    }

    /**
     * Decode a single entity reference. The caller must have already
     * verified that the input matches self::PATTERN — this is the cheap
     * path for callers that have one match in hand (e.g. the lexer
     * mode), avoiding the preg_replace_callback scan that decode() does.
     *
     * @param string $match A single entity reference, e.g. &#35; or &copy;
     * @return string The decoded codepoint(s), or the original literal
     *                bytes if the named entity is not recognised
     */
    public static function decodeOne(string $match): string
    {
        if ($match[1] === '#') {
            // Numeric refs are decoded explicitly rather than via
            // html_entity_decode: PHP returns the input unchanged for
            // U+0000, surrogates, and codepoints it considers unsafe
            // (including U+10FFFF and BMP noncharacters), where
            // CommonMark requires U+FFFD or the literal codepoint.
            return self::decodeNumeric(substr($match, 2, -1));
        }
        // Unknown names round-trip unchanged; the renderer's &-escape
        // turns them back into &amp;xxx; on output.
        return html_entity_decode($match, ENT_HTML5 | ENT_QUOTES, 'UTF-8');
    }

    /**
     * Decode the numeric portion of a numeric character reference, with
     * the CommonMark-mandated U+FFFD substitution for invalid codepoints
     * (zero, surrogate range, above U+10FFFF).
     *
     * @param string $body The digits between &# and ; — decimal digits,
     *                     or x/X followed by hex digits
     * @return string The corresponding UTF-8 codepoint, or U+FFFD when
     *                the codepoint is invalid
     */
    protected static function decodeNumeric(string $body): string
    {
        if ($body[0] === 'x' || $body[0] === 'X') {
            $cp = hexdec(substr($body, 1));
        } else {
            $cp = (int) $body;
        }

        if ($cp === 0 || $cp > 0x10FFFF || ($cp >= 0xD800 && $cp <= 0xDFFF)) {
            return self::REPLACEMENT;
        }

        $char = Unicode::toUtf8([$cp]);
        if ($char === false || $char === '') {
            return self::REPLACEMENT;
        }
        return $char;
    }
}
