<?php

namespace dokuwiki\Parsing\ParserMode;

class Windowssharelink extends AbstractMode
{

    protected $pattern;

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
    public function getSort()
    {
        return 350;
    }
}
