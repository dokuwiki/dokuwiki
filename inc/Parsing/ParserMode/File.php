<?php

namespace dokuwiki\Parsing\ParserMode;

class File extends Code
{
    /** @var string The call type used in addCall ('code' or 'file') */
    protected $type = 'file';

    /** @inheritdoc */
    public function getSort()
    {
        return 210;
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addEntryPattern('<file\b(?=.*</file>)', $mode, 'file');
    }

    /** @inheritdoc */
    public function postConnect()
    {
        $this->Lexer->addExitPattern('</file>', 'file');
    }
}
