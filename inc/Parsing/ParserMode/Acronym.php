<?php

namespace dokuwiki\Parsing\ParserMode;

class Acronym extends AbstractMode
{
    // A list
    protected $acronyms = array();
    protected $pattern = '';

    /**
     * Acronym constructor.
     *
     * @param string[] $acronyms
     */
    public function __construct($acronyms)
    {
        usort($acronyms, array($this,'compare'));
        $this->acronyms = $acronyms;
    }

    /** @inheritdoc */
    public function preConnect()
    {
        if (!count($this->acronyms)) return;

        $bound = '[\x00-\x2f\x3a-\x40\x5b-\x60\x7b-\x7f]';
        $acronyms = array_map(['\\dokuwiki\\Parsing\\Lexer\\Lexer', 'escape'], $this->acronyms);
        $this->pattern = '(?<=^|'.$bound.')(?:'.join('|', $acronyms).')(?='.$bound.')';
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        if (!count($this->acronyms)) return;

        if (strlen($this->pattern) > 0) {
            $this->Lexer->addSpecialPattern($this->pattern, $mode, 'acronym');
        }
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 240;
    }

    /**
     * sort callback to order by string length descending
     *
     * @param string $a
     * @param string $b
     *
     * @return int
     */
    protected function compare($a, $b)
    {
        $a_len = strlen($a);
        $b_len = strlen($b);
        if ($a_len > $b_len) {
            return -1;
        } elseif ($a_len < $b_len) {
            return 1;
        }

        return 0;
    }
}
