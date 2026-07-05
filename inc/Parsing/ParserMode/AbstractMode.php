<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;
use dokuwiki\Parsing\Lexer\Lexer;
use dokuwiki\Parsing\ModeRegistry;

/**
 * Base class for every parser mode (syntax component) in the Parser.
 *
 * Besides reducing the effort required to register modes with the Lexer, this
 * class defines the mode contract the engine relies on: getSort() and handle()
 * are abstract and must be implemented by every mode; preConnect(), connectTo(),
 * postConnect() and accepts() carry default implementations subclasses override
 * as needed. Parser, Handler and ModeRegistry type-hint this class directly.
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 */
abstract class AbstractMode
{
    /**
     * @var Lexer the lexer this mode registers its patterns with.
     *
     * Injected via setLexer() by Parser::addMode() / addBaseMode() before any
     * connect callback runs, so every core mode and plugin reads it as
     * $this->Lexer from connectTo(). External code reads it via getLexer().
     */
    protected Lexer $Lexer;

    /**
     * @var ModeRegistry the registry of the parse this mode belongs to.
     * Injected by Parser::addMode() before any connect/handle callback runs,
     * so subclasses may read $this->registry unconditionally from preConnect(),
     * connectTo(), postConnect(), handle() and accepts().
     */
    protected ModeRegistry $registry;

    /**
     * @var string[] mode names accepted as nested content inside this mode.
     *
     * Resolved once in setModeRegistry(): allowedCategories() mapped to concrete
     * mode names via the registry, then passed through filterAllowedModes(). A
     * subclass that does not use categories may instead assign this list
     * directly, in which case it is used as-is.
     */
    protected $allowedModes = [];

    //region Pattern building blocks

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
     * Maximum distance in bytes that closerAhead() scans for a closing
     * delimiter. Spans whose closer sits further away from the opener
     * (within the same paragraph) are not recognized and render as literal
     * text. The cap bounds the parser's worst case on delimiter-dense
     * input that never closes; see closerAhead() for the full reasoning.
     */
    protected const CLOSER_SCAN_LIMIT = 1024;

    /**
     * Builds a zero-width assertion: a closer matching the given pattern
     * occurs within the next CLOSER_SCAN_LIMIT characters and before the
     * next paragraph break.
     *
     * Example:
     *     return '\*\*(?=[^\s*])' . self::closerAhead('[^\s]\*\*');
     *
     * The scan advances one character at a time and stops at the first
     * position where the closer matches. The quantifier is possessive, so
     * the regex engine never backtracks into the scan and discards its
     * backtracking state as it goes: one scan costs time linear in the
     * distance to the closer and constant memory. On real content closers
     * sit near their openers, so parsing stays effectively linear.
     *
     * The cap exists because the lexer runs this lookahead once per opener
     * candidate: with an unbounded scan, a paragraph stuffed with openers
     * that never close costs openers-times-paragraph-length - quadratic
     * wall-clock on crafted input. The cap makes the worst case linear in
     * the document size (openers times the cap), trading away spans longer
     * than CLOSER_SCAN_LIMIT, which do not occur in real content. The
     * naive greedy form this replaces (consume everything up to the
     * paragraph break, then back up until the closer matches) had the
     * unbounded quadratic worst case, hit it on ordinary pages too (any
     * opener whose paragraph runs long re-scanned from the paragraph end)
     * and kept one backtracking frame per scanned character while doing
     * so: on large paragraphs that exhausted the PCRE JIT stack (the match
     * silently failed and the lexer emitted everything after it as plain
     * text) or, with the JIT disabled, the PHP memory limit. Truly linear
     * parsing without a span-length limit needs closer verification
     * outside the per-opener regex (CommonMark-style delimiter pairing).
     *
     * The closer pattern must not be able to match starting on whitespace:
     * the scan stops unconditionally at a paragraph break and only tests the
     * closer once there.
     *
     * The assertion is built from two lookaheads because of how PCRE
     * compiles counted repeats: {0,n} on a GROUP is compiled by replicating
     * the group's bytecode n times, so putting the cap directly on the
     * scanning group blows the maximum pattern size ("regular expression is
     * too large"). A counted repeat on a single dot compiles to one compact
     * opcode instead. So the first lookahead probes, via a bounded lazy dot,
     * that a closer occurs within the cap at all, and the second runs the
     * possessive stop-at-the-first-closer scan described above - unbounded
     * as written, but the first closer is within the cap when the probe
     * succeeded and the scan never runs past the first closer, so it is
     * bounded in effect.
     *
     * @param string $closer pattern for the closing delimiter, including any
     *                       flanking context (e.g. a preceding non-whitespace
     *                       character class)
     * @return string
     */
    protected static function closerAhead(string $closer): string
    {
        return '(?=.{0,' . self::CLOSER_SCAN_LIMIT . '}?' . $closer . ')'
            . '(?=(?:' . self::NOT_AT_PARA_BREAK . '(?!' . $closer . ').)*+' . $closer . ')';
    }

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

    //endregion

    //region Lexer connection

    /**
     * Returns a number used to determine in which order modes are added.
     *
     * @return int
     */
    abstract public function getSort();

    /**
     * Handle a matched token from the lexer.
     *
     * @param string $match The matched text
     * @param int $state The lexer state (DOKU_LEXER_ENTER, _EXIT, _MATCHED, etc.)
     * @param int $pos Byte position in the source
     * @param Handler $handler The handler (for addCall, status, etc.)
     * @return bool
     */
    abstract public function handle($match, $state, $pos, Handler $handler);

    /**
     * Called before any calls to connectTo.
     *
     * @return void
     */
    public function preConnect()
    {
    }

    /**
     * Connects the mode.
     *
     * @param string $mode
     * @return void
     */
    public function connectTo($mode)
    {
    }

    /**
     * Called after all calls to connectTo.
     *
     * @return void
     */
    public function postConnect()
    {
    }

    //endregion

    //region Dependency injection

    /**
     * Attach the registry of the parse this mode is taking part in and resolve
     * the set of modes this mode accepts as nested content.
     *
     * Called by Parser::addMode() / addBaseMode() as the mode joins the parser.
     * This is the earliest point the per-parse registry is available, so the
     * accepted-mode list is resolved here, once: allowedCategories() mapped to
     * concrete mode names via the registry taxonomy (complete by now, plugin
     * modes included), then passed through filterAllowedModes(). A subclass that
     * does not use categories has its directly-assigned $allowedModes used as-is.
     *
     * @param ModeRegistry $registry
     * @return void
     */
    public function setModeRegistry(ModeRegistry $registry): void
    {
        $this->registry = $registry;

        $categories = $this->allowedCategories();
        $modes = $categories
            ? $registry->getModesForCategories($categories)
            : (array) $this->allowedModes;
        $this->allowedModes = $this->filterAllowedModes($modes);
    }

    /**
     * Attach the lexer this mode registers its patterns with.
     *
     * Called by Parser::addMode() / addBaseMode() as the mode joins the parser,
     * before any connect callback runs.
     *
     * @param Lexer $lexer
     * @return void
     */
    public function setLexer(Lexer $lexer): void
    {
        $this->Lexer = $lexer;
    }

    /**
     * The lexer this mode registers its patterns with.
     *
     * @return Lexer
     */
    public function getLexer(): Lexer
    {
        return $this->Lexer;
    }

    //endregion

    //region Nested mode resolution

    /**
     * CATEGORY_* constants whose modes may nest inside this mode.
     *
     * Override to declare the categories this mode accepts; accepts() resolves
     * them to concrete mode names lazily (once the registry is attached) via
     * the parse's taxonomy. Returning [] means "use $this->allowedModes as-is"
     * (the default, empty unless a subclass sets it).
     *
     * @return string[]
     */
    protected function allowedCategories(): array
    {
        return [];
    }

    /**
     * Post-process the resolved allowedModes list.
     *
     * Override to remove entries (e.g. a mode excluding itself to prevent
     * self-nesting). Applied once, after allowedCategories() is resolved.
     *
     * @param string[] $modes
     * @return string[]
     */
    protected function filterAllowedModes(array $modes): array
    {
        return $modes;
    }

    /**
     * Check if the given mode is accepted inside this mode.
     *
     * The accepted-mode list is resolved once in setModeRegistry(); see there.
     *
     * @param string $mode
     * @return bool
     */
    public function accepts($mode)
    {
        return in_array($mode, $this->allowedModes, true);
    }

    //endregion
}
