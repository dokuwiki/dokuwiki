<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;
use dokuwiki\Parsing\Lexer\Lexer;

class Acronym extends AbstractMode
{
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
        $acronyms = array_map(Lexer::escape(...), $this->acronyms);
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
     * Sort callback to order by string length descending
     *
     * @param string $a
     * @param string $b
     * @return int
     */
    protected function compare($a, $b)
    {
        return strlen($b) <=> strlen($a);
    }
}
