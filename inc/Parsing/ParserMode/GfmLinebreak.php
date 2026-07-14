<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;

/**
 * GFM hard line break: two-or-more trailing spaces, or a single
 * backslash, immediately before a non-final newline.
 *
 * Both delimiter forms land in one mode because they share semantics
 * (emit linebreak), share the block-boundary rule (no break at the
 * end of a paragraph or other block), and share the next-line
 * leading-whitespace consumption (GFM strips it). Keeping all hard-
 * break logic in one pattern is cheaper than two and matches GFM
 * spec section 6.7 directly.
 *
 * Bypass inside code spans and fenced blocks falls out for free:
 * those are whole-span PROTECTED / FORMATTING modes that capture
 * their body in one regex match, so SUBSTITUTION patterns never see
 * the inner text — same mechanism that exempts GfmEscape from
 * code spans.
 *
 * Coexists with DokuWiki's Linebreak mode (also at sort 140). The two
 * delimiter shapes are disjoint: GfmLinebreak takes a single `\` (or
 * two+ spaces) before a non-final newline, DW Linebreak takes `\\`
 * before a space, tab, or newline.
 */
class GfmLinebreak extends AbstractMode
{
    /** @inheritdoc */
    public function getSort()
    {
        return 140;
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        // (?:[ ]{2,}|\\)            two+ spaces OR one backslash
        // \n                        the line ending
        // (?![ \t]*(?:\n|\z))       not at a paragraph break or EOF
        // [ \t]*                    swallow leading WS of the next line
        $this->Lexer->addSpecialPattern(
            '(?:[ ]{2,}|\\\\)\n(?![ \t]*(?:\n|\z))[ \t]*',
            $mode,
            'gfm_linebreak'
        );
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        $handler->addCall('linebreak', [], $pos);
        return true;
    }
}
