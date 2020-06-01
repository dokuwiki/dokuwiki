<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Lexer\Lexer;

/**
 * @fixme is this actually used?
 */
class Wordblock extends AbstractMode
{
    protected $badwords = array();
    protected $pattern = '';

    /**
     * Wordblock constructor.
     * @param $badwords
     */
    public function __construct($badwords)
    {
        $this->badwords = $badwords;
    }

    /** @inheritdoc */
    public function preConnect()
    {

        if (count($this->badwords) == 0 || $this->pattern != '') {
            return;
        }

        $sep = '';
        foreach ($this->badwords as $badword) {
            $this->pattern .= $sep.'(?<=\b)(?i)'. Lexer::escape($badword).'(?-i)(?=\b)';
            $sep = '|';
        }
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        if (strlen($this->pattern) > 0) {
            $this->Lexer->addSpecialPattern($this->pattern, $mode, 'wordblock');
        }
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 250;
    }
}
