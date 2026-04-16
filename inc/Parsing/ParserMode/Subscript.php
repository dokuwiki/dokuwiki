<?php

namespace dokuwiki\Parsing\ParserMode;

class Subscript extends AbstractFormatting
{
    /** @inheritdoc */
    protected function getModeName(): string
    {
        return 'subscript';
    }

    /** @inheritdoc */
    protected function getEntryPattern(): string
    {
        return '<sub>(?=.*</sub>)';
    }

    /** @inheritdoc */
    protected function getExitPattern(): string
    {
        return '</sub>';
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 110;
    }
}
