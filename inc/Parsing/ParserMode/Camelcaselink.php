<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;

class Camelcaselink extends AbstractMode
{
    /** @inheritdoc */
    public function getSort()
    {
        return 290;
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern(
            '\b[A-Z]+[a-z]+[A-Z][A-Za-z]*\b',
            $mode,
            'camelcaselink'
        );
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        $handler->addCall('camelcaselink', [$match], $pos);
        return true;
    }
}
