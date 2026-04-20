<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Lexer\Lexer;

/**
 * This class and all the subclasses below are used to reduce the effort required to register
 * modes with the Lexer.
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 */
abstract class AbstractMode implements ModeInterface
{
    /** @var Lexer $Lexer will be injected on loading FIXME this should be done by setter */
    public $Lexer;
    protected $allowedModes = [];

    /**
     * Regex snippet: quantified group matching any character that does not
     * start a paragraph break (a blank line — two newlines possibly separated
     * by horizontal whitespace).
     *
     * The DokuWiki lexer compiles all mode patterns with the `s` (DOTALL) flag
     * via ParallelRegex::getPerlMatchingFlags(), so a plain `.*` inside an
     * entry-pattern lookahead matches across newlines and lets an unclosed
     * delimiter greedily consume following paragraphs. Use this constant in
     * lookaheads that scan for a closing delimiter to keep formatting inside
     * a single paragraph.
     *
     * Example:
     *     return '\*\*(?=' . self::CONTENT_UNTIL_PARA . '\*\*)';
     */
    protected const CONTENT_UNTIL_PARA = '(?:(?!\n[ \t]*\n).)*';

    /** @inheritdoc */
    abstract public function getSort();

    /** @inheritdoc */
    public function preConnect()
    {
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
    }

    /** @inheritdoc */
    public function postConnect()
    {
    }

    /** @inheritdoc */
    public function accepts($mode)
    {
        return in_array($mode, (array) $this->allowedModes);
    }
}
