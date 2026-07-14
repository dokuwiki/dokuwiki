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
        return self::NO_WORD_BEFORE
            . '_(?=[^\s_])';
    }

    /** @inheritdoc */
    protected function getExitPattern(): string
    {
        return '(?<=[^\s])_' . self::NO_WORD_AFTER;
    }
}
