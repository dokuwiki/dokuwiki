<?php

namespace dokuwiki\Parsing\ParserMode;

class Monospace extends AbstractFormatting
{
    /** @inheritdoc */
    public function getSort()
    {
        return 100;
    }

    /** @inheritdoc */
    protected function getModeName(): string
    {
        return 'monospace';
    }

    /** @inheritdoc */
    protected function getEntryPattern(): string
    {
        return '\x27\x27(?=.*\x27\x27)';
    }

    /** @inheritdoc */
    protected function getExitPattern(): string
    {
        return '\x27\x27';
    }
}
