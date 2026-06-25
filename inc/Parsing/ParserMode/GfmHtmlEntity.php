<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;
use dokuwiki\Parsing\Helpers\HtmlEntity;

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
 * Decoding semantics live in {@see HtmlEntity}; this mode is a thin
 * wrapper that exposes them to the inline lexer. Whole-span PROTECTED
 * modes (GfmCode, GfmLink, ...) capture their body in one regex shot
 * and bypass this mode, so they call HtmlEntity::decode() directly on
 * the captured slice.
 *
 * Category SUBSTITUTION so the mode is reachable in every container
 * that allows substitutions (paragraphs, formatting, list items,
 * table cells, headers). Code spans and code blocks live in
 * CATEGORY_PROTECTED and reject SUBSTITUTION, so entities stay literal
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
    /** @inheritdoc */
    public function getSort()
    {
        return 255;
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern(HtmlEntity::PATTERN, $mode, 'gfm_html_entity');
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        $handler->addCall('cdata', [HtmlEntity::decodeOne($match)], $pos);
        return true;
    }
}
