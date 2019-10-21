<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Lexer\Lexer;

class Smiley extends AbstractMode
{
    protected $smileys = array();
    protected $pattern = '';

    /**
     * Smiley constructor.
     * @param string[] $smileys
     */
    public function __construct($smileys)
    {
        $this->smileys = $smileys;
    }

    /** @inheritdoc */
    public function preConnect()
    {
        if (!count($this->smileys) || $this->pattern != '') return;

        $sep = '';
        foreach ($this->smileys as $smiley) {
            $this->pattern .= $sep.'(?<=\W|^)'. Lexer::escape($smiley).'(?=\W|$)';
            $sep = '|';
        }
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        if (!count($this->smileys)) return;

        if (strlen($this->pattern) > 0) {
            $this->Lexer->addSpecialPattern($this->pattern, $mode, 'smiley');
        }
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 230;
    }
}
