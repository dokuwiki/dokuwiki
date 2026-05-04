<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;

/**
 * Horizontal rule — single mode covering both DokuWiki and GFM dialects.
 *
 * Replaces the legacy DW Hr mode and is always loaded across all four
 * `$conf['syntax']` settings; the opener pattern self-narrows based on
 * syntax preference. Pure `dokuwiki` keeps its historical 4-or-more
 * dashes rule. The other three settings accept 3-or-more of any one
 * GFM thematic-break character (`-`, `*`, `_`).
 *
 * No leading, trailing, or internal whitespace in either flavor: the
 * delimiter run must be a bare line. The DW pattern's old `[ \t]*`
 * leading-whitespace tolerance was inert in practice for everything
 * but 0-1 spaces (Preformatted at sort 20 intercepts ≥ 2 spaces or any
 * tab); dropping it costs nothing real and keeps both flavors strict.
 *
 * Emits the existing `hr` handler call so renderers, downloads and
 * call shape are unchanged.
 */
class GfmHr extends AbstractMode
{
    /** @inheritdoc */
    public function getSort()
    {
        return 160;
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        global $conf;

        $pattern = $conf['syntax'] === 'dw'
            ? '\n-{4,}(?=\n)'
            : '\n(?:-{3,}|\*{3,}|_{3,})(?=\n)';

        $this->Lexer->addSpecialPattern($pattern, $mode, 'gfm_hr');
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        $handler->addCall('hr', [], $pos);
        return true;
    }
}
