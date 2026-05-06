<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;

class Linebreak extends AbstractMode
{
    /** @inheritdoc */
    public function getSort()
    {
        return 140;
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern('\x5C{2}(?:[ \t]|(?=\n))', $mode, 'linebreak');
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        $handler->addCall('linebreak', [], $pos);
        return true;
    }
}
