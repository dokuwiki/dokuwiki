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
     * @see https://github.com/dokuwiki/dokuwiki/issues/384
     * @see https://github.com/dokuwiki/dokuwiki/issues/763
     * @see https://github.com/dokuwiki/dokuwiki/issues/1468
     *
     * Flanking rules (simplified): opener must be followed by non-whitespace
     * non-`/`; closer must be preceded by non-whitespace non-colon (the colon
     * exclusion protects `http://`-style URLs).
     */
    protected function getEntryPattern(): string
    {
        return '//(?=[^\s/])(?=' . self::CONTENT_UNTIL_PARA . '[^\s:]//)';
    }

    /** @inheritdoc */
    protected function getExitPattern(): string
    {
        return '(?<=[^\s])//';
    }
}
