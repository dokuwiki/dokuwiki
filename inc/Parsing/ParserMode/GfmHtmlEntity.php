<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;
use dokuwiki\Utf8\Unicode;

/**
 * GFM HTML entity references: numeric (`&#nnn;` and `&#xhhh;`) and
 * HTML5 named (`&copy;`, `&AElig;`, `&ngE;`, ...) decode to the
 * corresponding Unicode codepoint(s) and ride out as cdata.
 *
 * Distinct from the typography Entity mode, which is renderer-side
 * configurable (entities.conf maps `(c)` to `©` etc.). HTML entity
 * references are not configurable - their meaning is fixed by the
 * HTML5 / Unicode specs - so decoding happens at parse time and the
 * renderer needs no changes.
 *
 * Per CommonMark, codepoint 0, codepoints above U+10FFFF, and the
 * surrogate range U+D800..U+DFFF all map to U+FFFD (REPLACEMENT
 * CHARACTER) for numeric references. Unknown named references stay
 * literal: the original `&xxx;` is emitted as cdata and the renderer's
 * &-escaping turns it into `&amp;xxx;` on output.
 *
 * Category SUBSTITION so the mode is reachable in every container
 * that allows substitutions (paragraphs, formatting, list items,
 * table cells, headers). Code spans and code blocks live in
 * CATEGORY_PROTECTED and reject SUBSTITION, so entities stay literal
 * there - matching CommonMark's rule that entities are not recognized
 * in code.
 *
 * Side benefit: by consuming the entire entity run before any
 * structural pattern sees it, this mode automatically enforces the
 * spec rule that numeric references cannot stand in for structural
 * markers. `&#42;foo&#42;` decodes to literal `*foo*` text and never
 * triggers emphasis; `&#42; foo` decodes to literal `* foo` and never
 * starts a list.
 */
class GfmHtmlEntity extends AbstractMode
{
    protected const REPLACEMENT = "\u{FFFD}";

    public function __construct()
    {
        $this->allowedModes = [];
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 255;
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern(
            '&(?:#(?:[0-9]{1,7}|[xX][0-9a-fA-F]{1,6})|[a-zA-Z][a-zA-Z0-9]{0,30});',
            $mode,
            'gfm_html_entity'
        );
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        if ($match[1] === '#') {
            // Numeric refs are decoded explicitly rather than via
            // html_entity_decode: PHP returns the input unchanged for
            // U+0000, surrogates, and codepoints it considers unsafe
            // (including U+10FFFF and BMP noncharacters), where
            // CommonMark requires U+FFFD or the literal codepoint.
            $char = $this->decodeNumeric(substr($match, 2, -1));
        } else {
            // Unknown names round-trip unchanged; the renderer's &-escape
            // turns them back into &xxx; on output.
            $char = html_entity_decode($match, ENT_HTML5 | ENT_QUOTES, 'UTF-8');
        }

        $handler->addCall('cdata', [$char], $pos);
        return true;
    }

    protected function decodeNumeric(string $body): string
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
