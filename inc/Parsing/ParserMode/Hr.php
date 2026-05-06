<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;

class Hr extends AbstractMode
{
    /** @inheritdoc */
    public function getSort()
    {
        return 160;
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern('\n[ \t]*-{4,}[ \t]*(?=\n)', $mode, 'hr');
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        $handler->addCall('hr', [], $pos);
        return true;
    }
}
