<?php

namespace dokuwiki\Parsing\ParserMode;

class Internallink extends AbstractMode
{

    /** @inheritdoc */
    public function connectTo($mode)
    {
        // Word boundaries?
        $this->Lexer->addSpecialPattern("\[\[.*?\]\](?!\])", $mode, 'internallink');
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 300;
    }
}
