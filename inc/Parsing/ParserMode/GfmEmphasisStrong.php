<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;

/**
 * GFM / CommonMark em-wrapping-strong via triple asterisks: `***text***`.
 *
 * Renders as <em><strong>text</strong></em>. Only the exact 3+3 symmetric
 * variant is supported. Longer symmetric runs (`****foo****`,
 * `******foo******`) or asymmetric runs (`***foo**`) require CommonMark's
 * full delimiter-pairing algorithm and are out of scope.
 *
 * Sort 65 is below Strong (70) so this mode wins the lexer race for
 * `***...***` patterns.
 */
class GfmEmphasisStrong extends AbstractFormatting
{
    /** @inheritdoc */
    public function getSort()
    {
        return 65;
    }

    /** @inheritdoc */
    protected function getModeName(): string
    {
        return 'gfm_emphasis_strong';
    }

    /** @inheritdoc */
    protected function getEntryPattern(): string
    {
        // Broken down:
        //   (?<!\*)                  — opener not preceded by `*` (so we
        //                              don't match inside `****...` runs)
        //   \*\*\*                   — exactly three opening `*`
        //   (?=[^\s*])               — next body char: not whitespace, not `*`
        //                              (flanking-opener rule)
        return '(?<!\*)\*\*\*(?=[^\s*])';
    }

    /** @inheritdoc */
    protected function getExitPattern(): string
    {
        return '(?<=[^\s])\*\*\*(?!\*)';
    }

    /**
     * Emit em wrapping strong (and their closers in reverse order).
     * Overridden because AbstractFormatting's default emits a single
     * open/close pair — we need two each.
     *
     * @inheritdoc
     */
    public function handle($match, $state, $pos, Handler $handler)
    {
        switch ($state) {
            case DOKU_LEXER_ENTER:
                $handler->addCall('emphasis_open', [], $pos);
                $handler->addCall('strong_open', [], $pos);
                break;
            case DOKU_LEXER_EXIT:
                $handler->addCall('strong_close', [], $pos);
                $handler->addCall('emphasis_close', [], $pos);
                break;
            case DOKU_LEXER_UNMATCHED:
                $handler->addCall('cdata', [$match], $pos);
                break;
        }
        return true;
    }
}
