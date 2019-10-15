<?php

namespace dokuwiki\Parsing\ParserMode;

class Linebreak extends AbstractMode
{

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern('\x5C{2}(?:[ \t]|(?=\n))', $mode, 'linebreak');
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 140;
    }
}
