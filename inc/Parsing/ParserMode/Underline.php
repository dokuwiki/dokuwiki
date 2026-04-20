<?php

namespace dokuwiki\Parsing\ParserMode;

class Underline extends AbstractFormatting
{
    /** @inheritdoc */
    public function getSort()
    {
        return 90;
    }

    /** @inheritdoc */
    protected function getModeName(): string
    {
        return 'underline';
    }

    /** @inheritdoc */
    protected function getEntryPattern(): string
    {
        return '__(?=' . self::CONTENT_UNTIL_PARA . '__)';
    }

    /** @inheritdoc */
    protected function getExitPattern(): string
    {
        return '__';
    }
}
