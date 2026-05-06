<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;

class Nocache extends AbstractMode
{
    /** @inheritdoc */
    public function getSort()
    {
        return 40;
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern('~~NOCACHE~~', $mode, 'nocache');
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        $handler->addCall('nocache', [], $pos);
        return true;
    }
}
