<?php

namespace dokuwiki\Parsing\ParserMode;

class Nocache extends AbstractMode
{

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern('~~NOCACHE~~', $mode, 'nocache');
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 40;
    }
}
