<?php

namespace dokuwiki\Parsing\ParserMode;

class Subscript extends AbstractFormatting
{
    /** @inheritdoc */
    public function getSort()
    {
        return 110;
    }

    /** @inheritdoc */
    protected function getModeName(): string
    {
        return 'subscript';
    }

    /** @inheritdoc */
    protected function getEntryPattern(): string
    {
        return '<sub>(?=' . self::CONTENT_UNTIL_PARA . '</sub>)';
    }

    /** @inheritdoc */
    protected function getExitPattern(): string
    {
        return '</sub>';
    }
}
