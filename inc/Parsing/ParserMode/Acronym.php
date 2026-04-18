<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;

class Acronym extends AbstractMode
{
    // A list
    protected $acronyms = [];
    protected $pattern = '';

    /**
     * Acronym constructor.
     *
     * @param string[] $acronyms
     */
    public function __construct($acronyms)
    {
        usort($acronyms, $this->compare(...));
        $this->acronyms = $acronyms;
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 240;
    }

    /** @inheritdoc */
    public function preConnect()
    {
        if (!count($this->acronyms)) return;

        $bound = '[\x00-\x2f\x3a-\x40\x5b-\x60\x7b-\x7f]';
        $acronyms = array_map(['\\dokuwiki\\Parsing\\Lexer\\Lexer', 'escape'], $this->acronyms);
        $this->pattern = '(?<=^|' . $bound . ')(?:' . implode('|', $acronyms) . ')(?=' . $bound . ')';
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        if (!count($this->acronyms)) return;

        if ((string) $this->pattern !== '') {
            $this->Lexer->addSpecialPattern($this->pattern, $mode, 'acronym');
        }
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        $handler->addCall('acronym', [$match], $pos);
        return true;
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
