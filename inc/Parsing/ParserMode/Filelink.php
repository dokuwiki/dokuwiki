<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;

class Filelink extends AbstractMode
{
    protected $pattern;

    /** @inheritdoc */
    public function getSort()
    {
        return 360;
    }

    /** @inheritdoc */
    public function preConnect()
    {

        $ltrs = '\w';
        $gunk = '/\#~:.?+=&%@!\-';
        $punc = '.:?\-;,';
        $any  = $ltrs . $gunk . $punc;

        $this->pattern = '\b(?i)file(?-i)://[' . $any . ']+?[' .
            $punc . ']*[^' . $any . ']';
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern(
            $this->pattern,
            $mode,
            'filelink'
        );
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        $handler->addCall('filelink', [$match, null], $pos);
        return true;
    }
}
