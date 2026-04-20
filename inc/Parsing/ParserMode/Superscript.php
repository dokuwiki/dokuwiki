<?php

namespace dokuwiki\Parsing\ParserMode;

class Superscript extends AbstractFormatting
{
    /** @inheritdoc */
    public function getSort()
    {
        return 120;
    }

    /** @inheritdoc */
    protected function getModeName(): string
    {
        return 'superscript';
    }

    /** @inheritdoc */
    protected function getEntryPattern(): string
    {
        return '<sup>(?=[^\s])(?=' . self::CONTENT_UNTIL_PARA . '[^\s]</sup>)';
    }

    /** @inheritdoc */
    protected function getExitPattern(): string
    {
        return '(?<=[^\s])</sup>';
    }
}
