<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;
use dokuwiki\Utf8\Unicode;

/**
 * GFM numeric character references: `&#nnnn;` (decimal, 1-7 digits)
 * and `&#xhhhh;` / `&#Xhhhh;` (hex, 1-6 digits) decode to the
 * corresponding Unicode codepoint.
 *
 * Distinct from the typography Entity mode, which is renderer-side
 * configurable (entities.conf maps `(c)` to `©` etc.). Numeric
 * references are not configurable — `&#35;` always means `#` — so
 * decoding happens at parse time and the result rides on a plain
 * `cdata` call. No renderer changes are needed because every
 * renderer already handles cdata as plain text with the appropriate
 * HTML escaping.
 *
 * Per CommonMark, codepoint 0, codepoints above U+10FFFF, and the
 * surrogate range U+D800..U+DFFF all map to U+FFFD (REPLACEMENT
 * CHARACTER).
 *
 * Category SUBSTITION so the mode is reachable in every container
 * that allows substitutions (paragraphs, formatting, list items,
 * table cells, headers). Code spans and code blocks live in
 * CATEGORY_PROTECTED and reject SUBSTITION, so numeric refs stay
 * literal there — matching CommonMark's rule that entities are not
 * recognized in code.
 *
 * Side benefit: by consuming the entire `&#nnn;` run before any
 * structural pattern sees it, this mode automatically enforces the
 * spec rule that numeric references cannot stand in for structural
 * markers. `&#42;foo&#42;` decodes to literal `*foo*` text and never
 * triggers emphasis; `&#42; foo` decodes to literal `* foo` and
 * never starts a list.
 */
class GfmNumericEntity extends AbstractMode
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
            '&#(?:[0-9]{1,7}|[xX][0-9a-fA-F]{1,6});',
            $mode,
            'gfm_numeric_entity'
        );
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        $body = substr($match, 2, -1);
        if ($body[0] === 'x' || $body[0] === 'X') {
            $cp = hexdec(substr($body, 1));
        } else {
            $cp = (int) $body;
        }

        if ($cp === 0 || $cp > 0x10FFFF || ($cp >= 0xD800 && $cp <= 0xDFFF)) {
            $char = self::REPLACEMENT;
        } else {
            $char = Unicode::toUtf8([$cp]);
            if ($char === false || $char === '') {
                $char = self::REPLACEMENT;
            }
        }

        $handler->addCall('cdata', [$char], $pos);
        return true;
    }
}
