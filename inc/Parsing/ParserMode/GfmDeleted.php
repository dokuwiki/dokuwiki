<?php

namespace dokuwiki\Parsing\ParserMode;

/**
 * GFM strikethrough via paired double tildes: `~~text~~`.
 *
 * Emits deleted_open / deleted_close — the same instructions as DokuWiki's
 * Deleted (`<del>…</del>`), so both syntaxes render as <del>.
 */
class GfmDeleted extends AbstractFormatting
{
    /** @inheritdoc */
    public function getSort()
    {
        return 130;
    }

    /** @inheritdoc */
    protected function getModeName(): string
    {
        return 'gfm_deleted';
    }

    /** @inheritdoc */
    protected function getInstructionName(): string
    {
        return 'deleted';
    }

    /** @inheritdoc */
    protected function getEntryPattern(): string
    {
        // Broken down:
        //   (?<!~)                 — not preceded by `~` (runs of 3+ tildes
        //                            are fenced-code markers, not strike)
        //   ~~                     — two opening tildes
        //   (?=[^\s~])             — next body char: not whitespace, not `~`
        //   closerAhead(           — lookahead: a valid closer must exist
        //                            before the next blank line
        //     [^\s]~~              —   non-whitespace, then closing `~~`
        //     (?!~)                —   and not followed by another `~`
        //   )
        return '(?<!~)~~(?=[^\s~])'
            . self::closerAhead('[^\s]~~(?!~)');
    }

    /** @inheritdoc */
    protected function getExitPattern(): string
    {
        return '(?<=[^\s])~~(?!~)';
    }
}
