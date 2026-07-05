<?php

namespace dokuwiki\Parsing\ParserMode;

/**
 * GFM / CommonMark strong emphasis via double underscores: `__text__`.
 *
 * Only loaded when Markdown is the only or preferred syntax
 *
 * Emits strong_open / strong_close — the same instructions as DokuWiki's
 * Strong (`**`), so both delimiters render as <strong>.
 */
class GfmStrongUnderscore extends AbstractFormatting
{
    /** @inheritdoc */
    public function getSort()
    {
        return 70;
    }

    /** @inheritdoc */
    protected function getModeName(): string
    {
        return 'gfm_strong_underscore';
    }

    /** @inheritdoc */
    protected function getInstructionName(): string
    {
        return 'strong';
    }

    /** @inheritdoc */
    protected function getEntryPattern(): string
    {
        return self::NO_WORD_BEFORE
            . '__(?=[^\s_])'
            . self::closerAhead('[^\s]__' . self::NO_WORD_AFTER);
    }

    /** @inheritdoc */
    protected function getExitPattern(): string
    {
        return '(?<=[^\s])__' . self::NO_WORD_AFTER;
    }
}
