<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;

class Windowssharelink extends AbstractMode
{
    protected $pattern;

    /** @inheritdoc */
    public function getSort()
    {
        return 350;
    }

    /** @inheritdoc */
    public function preConnect()
    {
        $this->pattern = "\\\\\\\\\w+?(?:\\\\[\w\-$]+)+";
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern(
            $this->pattern,
            $mode,
            'windowssharelink'
        );
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        $handler->addCall('windowssharelink', [$match, null], $pos);
        return true;
    }
}
