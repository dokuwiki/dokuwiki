<?php

namespace dokuwiki\Parsing\ParserMode;

/**
 * GFM / CommonMark emphasis via single underscores: `_text_`.
 *
 * Only loaded when Markdown is the only or preferred syntax
 *
 * Emits emphasis_open / emphasis_close — the same instructions as DokuWiki's
 * Emphasis (`//`) and GfmEmphasis (`*`), so all three render as <em>.
 */
class GfmEmphasisUnderscore extends AbstractFormatting
{
    /** @inheritdoc */
    public function getSort()
    {
        return 80;
    }

    /** @inheritdoc */
    protected function getModeName(): string
    {
        return 'gfm_emphasis_underscore';
    }

    /** @inheritdoc */
    protected function getInstructionName(): string
    {
        return 'emphasis';
    }

    /** @inheritdoc */
    protected function getEntryPattern(): string
    {
        // Entry requires a valid closing `_` (non-whitespace char before it AND
        // NO_WORD_AFTER following). Otherwise emphasis would open with no way
        // to ever close (or close at an invalid position).
        return self::NO_WORD_BEFORE
            . '_(?=[^\s_])'
            . self::closerAhead('[^\s]_' . self::NO_WORD_AFTER);
    }

    /** @inheritdoc */
    protected function getExitPattern(): string
    {
        return '(?<=[^\s])_' . self::NO_WORD_AFTER;
    }
}
