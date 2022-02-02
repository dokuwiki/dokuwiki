<?php

namespace dokuwiki\Parsing\ParserMode;

class Hr extends AbstractMode
{

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern('\n[ \t]*-{4,}[ \t]*(?=\n)', $mode, 'hr');
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 160;
    }
}
