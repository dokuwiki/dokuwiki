<?php

namespace dokuwiki\Parsing\ParserMode;

class Superscript extends AbstractFormatting
{
    /** @inheritdoc */
    protected function getModeName(): string
    {
        return 'superscript';
    }

    /** @inheritdoc */
    protected function getEntryPattern(): string
    {
        return '<sup>(?=.*</sup>)';
    }

    /** @inheritdoc */
    protected function getExitPattern(): string
    {
        return '</sup>';
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 120;
    }
}
