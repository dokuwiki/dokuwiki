<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;
use dokuwiki\Parsing\Lexer\Lexer;

class Smiley extends AbstractMode
{
    protected $smileys = [];
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
    public function getSort()
    {
        return 230;
    }

    /** @inheritdoc */
    public function preConnect()
    {
        if (!count($this->smileys) || $this->pattern != '') return;

        $sep = '';
        foreach ($this->smileys as $smiley) {
            $this->pattern .= $sep . '(?<=\W|^)' . Lexer::escape($smiley) . '(?=\W|$)';
            $sep = '|';
        }
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        if (!count($this->smileys)) return;

        if ((string) $this->pattern !== '') {
            $this->Lexer->addSpecialPattern($this->pattern, $mode, 'smiley');
        }
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        $handler->addCall('smiley', [$match], $pos);
        return true;
    }
}
