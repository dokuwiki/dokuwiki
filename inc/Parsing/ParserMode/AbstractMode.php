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
     * Zero-width assertion: not at the start of a paragraph break.
     *
     * Paragraph boundaries are blank lines — two newlines possibly separated
     * by horizontal whitespace. The lexer compiles all patterns with the `s`
     * (DOTALL) flag, so a plain `.*` inside an entry-pattern lookahead would
     * match across blank lines and let an unclosed delimiter greedily consume
     * following paragraphs. Place this assertion before a character class to
     * stop the match at a paragraph boundary.
     */
    protected const NOT_AT_PARA_BREAK = '(?!\n[ \t]*\n)';

    /**
     * Quantified group matching any character that does not start a paragraph
     * break. Convenience for the common case of "consume until paragraph end".
     *
     * Example:
     *     return '\*\*(?=' . self::CONTENT_UNTIL_PARA . '\*\*)';
     */
    protected const CONTENT_UNTIL_PARA = '(?:' . self::NOT_AT_PARA_BREAK . '.)*';

    /**
     * Character class: a single "non-word" character — ASCII whitespace or
     * any ASCII punctuation character except the underscore.
     *
     * The `_` is excluded because it is itself a delimiter for emphasis in
     * GFM/CommonMark; treating it as non-word would let `__foo` incorrectly
     * open emphasis at the second `_`.
     *
     * Multibyte rationale: the lexer compiles patterns without the `u` flag,
     * so UTF-8 is treated as individual bytes. Multibyte characters begin
     * with bytes >= 0x80, which fall outside every ASCII character class.
     * Checking that the surrounding context matches NON_WORD_CHAR positively
     * therefore correctly treats multibyte letters as word-like — preventing
     * intraword matches in non-Latin text (e.g. `für_etwas`, `日本_語`)
     * without requiring `u` flag support across the whole lexer.
     */
    protected const NON_WORD_CHAR = '[\s!"#$%&\'()*+,\-./:;<=>?@\[\\\\\]^`{|}~]';

    /**
     * Zero-width assertion: current position is preceded by a non-word
     * character, or is at the start of input/line. See {@see self::NON_WORD_CHAR}
     * for the multibyte reasoning.
     */
    protected const NO_WORD_BEFORE = '(?:^|(?<=' . self::NON_WORD_CHAR . '))';

    /**
     * Zero-width assertion: current position is followed by a non-word
     * character, or is at the end of input. Complement to
     * {@see self::NO_WORD_BEFORE}.
     */
    protected const NO_WORD_AFTER = '(?:\z|(?=' . self::NON_WORD_CHAR . '))';

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
