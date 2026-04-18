<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;

class Notoc extends AbstractMode
{
    /** @inheritdoc */
    public function getSort()
    {
        return 30;
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern('~~NOTOC~~', $mode, 'notoc');
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        $handler->addCall('notoc', [], $pos);
        return true;
    }
}
