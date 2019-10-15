<?php

namespace dokuwiki\Parsing\ParserMode;

class Php extends AbstractMode
{

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addEntryPattern('<php>(?=.*</php>)', $mode, 'php');
        $this->Lexer->addEntryPattern('<PHP>(?=.*</PHP>)', $mode, 'phpblock');
    }

    /** @inheritdoc */
    public function postConnect()
    {
        $this->Lexer->addExitPattern('</php>', 'php');
        $this->Lexer->addExitPattern('</PHP>', 'phpblock');
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 180;
    }
}
