<?php

namespace dokuwiki\Parsing\ParserMode;

class Strong extends AbstractFormatting
{
    /** @inheritdoc */
    public function getSort()
    {
        return 70;
    }

    /** @inheritdoc */
    protected function getModeName(): string
    {
        return 'strong';
    }

    /** @inheritdoc */
    protected function getEntryPattern(): string
    {
        return '\*\*(?=.*\*\*)';
    }

    /** @inheritdoc */
    protected function getExitPattern(): string
    {
        return '\*\*';
    }
}
