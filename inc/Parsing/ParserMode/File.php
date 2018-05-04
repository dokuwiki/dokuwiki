<?php

namespace dokuwiki\Parsing\ParserMode;

class File extends AbstractMode
{

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

    /** @inheritdoc */
    public function getSort()
    {
        return 210;
    }
}
