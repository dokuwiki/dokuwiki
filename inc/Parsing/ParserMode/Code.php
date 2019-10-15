<?php

namespace dokuwiki\Parsing\ParserMode;

class Code extends AbstractMode
{

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addEntryPattern('<code\b(?=.*</code>)', $mode, 'code');
    }

    /** @inheritdoc */
    public function postConnect()
    {
        $this->Lexer->addExitPattern('</code>', 'code');
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 200;
    }
}
