<?php

namespace dokuwiki\Parsing\ParserMode;

class Emphasis extends AbstractFormatting
{
    /** @inheritdoc */
    public function getSort()
    {
        return 80;
    }

    /** @inheritdoc */
    protected function getModeName(): string
    {
        return 'emphasis';
    }

    /**
     * @inheritdoc
     *
     * Flanking rules: the opener must be followed by a non-whitespace
     * character other than a slash, and the closer must be preceded by a
     * non-whitespace character.
     */
    protected function getEntryPattern(): string
    {
        return '//(?=[^\s/])';
    }

    /** @inheritdoc */
    protected function getExitPattern(): string
    {
        return '(?<=[^\s])//';
    }
}
