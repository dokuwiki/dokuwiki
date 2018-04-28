<?php

namespace dokuwiki\ParserMode;

class Notoc extends AbstractMode
{

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern('~~NOTOC~~', $mode, 'notoc');
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 30;
    }
}
