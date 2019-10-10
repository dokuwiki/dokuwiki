<?php

namespace dokuwiki\Parsing\ParserMode;

class Filelink extends AbstractMode
{

    protected $pattern;

    /** @inheritdoc */
    public function preConnect()
    {

        $ltrs = '\w';
        $gunk = '/\#~:.?+=&%@!\-';
        $punc = '.:?\-;,';
        $host = $ltrs.$punc;
        $any  = $ltrs.$gunk.$punc;

        $this->pattern = '\b(?i)file(?-i)://['.$any.']+?['.
            $punc.']*[^'.$any.']';
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
    public function getSort()
    {
        return 360;
    }
}
