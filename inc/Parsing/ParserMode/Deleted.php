<?php

namespace dokuwiki\Parsing\ParserMode;

class Deleted extends AbstractFormatting
{
    /** @inheritdoc */
    public function getSort()
    {
        return 130;
    }

    /** @inheritdoc */
    protected function getModeName(): string
    {
        return 'deleted';
    }

    /** @inheritdoc */
    protected function getEntryPattern(): string
    {
        return '<del>(?=.*</del>)';
    }

    /** @inheritdoc */
    protected function getExitPattern(): string
    {
        return '</del>';
    }
}
