<?php

namespace dokuwiki\Parsing\ParserMode;

class Unformatted extends AbstractMode
{

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addEntryPattern('<nowiki>(?=.*</nowiki>)', $mode, 'unformatted');
        $this->Lexer->addEntryPattern('%%(?=.*%%)', $mode, 'unformattedalt');
    }

    /** @inheritdoc */
    public function postConnect()
    {
        $this->Lexer->addExitPattern('</nowiki>', 'unformatted');
        $this->Lexer->addExitPattern('%%', 'unformattedalt');
        $this->Lexer->mapHandler('unformattedalt', 'unformatted');
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 170;
    }
}
