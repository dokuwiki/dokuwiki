<?php

namespace dokuwiki\Parsing\ParserMode;

class Externallink extends AbstractMode
{
    protected $schemes = array();
    protected $patterns = array();

    /** @inheritdoc */
    public function preConnect()
    {
        if (count($this->patterns)) return;

        $ltrs = '\w';
        $gunk = '/\#~:.?+=&%@!\-\[\]';
        $punc = '.:?\-;,';
        $host = $ltrs.$punc;
        $any  = $ltrs.$gunk.$punc;

        $this->schemes = getSchemes();
        foreach ($this->schemes as $scheme) {
            $this->patterns[] = '\b(?i)'.$scheme.'(?-i)://['.$any.']+?(?=['.$punc.']*[^'.$any.'])';
        }

        $this->patterns[] = '(?<=\s)(?i)www?(?-i)\.['.$host.']+?\.['.$host.']+?['.$any.']+?(?=['.$punc.']*[^'.$any.'])';
        $this->patterns[] = '(?<=\s)(?i)ftp?(?-i)\.['.$host.']+?\.['.$host.']+?['.$any.']+?(?=['.$punc.']*[^'.$any.'])';
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {

        foreach ($this->patterns as $pattern) {
            $this->Lexer->addSpecialPattern($pattern, $mode, 'externallink');
        }
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 330;
    }
}
