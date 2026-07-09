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
     * mode names via the registry and merged with any names a subclass assigned
     * to this list directly, then passed through filterAllowedModes(). A subclass
     * may therefore combine categories with individual mode names (e.g. a sibling
     * component), or, when it declares no categories, assign the list directly and
     * have it used as-is.
     */
    protected $allowedModes = [];

    //region Pattern building blocks

    /**
     * Zero-width assertion: not at the start of a paragraph break
     * (Lexer::PARA_BREAK).
     *
     * The lexer compiles all patterns with the `s` (DOTALL) flag, so a
     * plain `.*` inside an entry-pattern lookahead would match across blank
     * lines and let an unclosed delimiter greedily consume following
     * paragraphs. Place this assertion before a character class to stop the
     * match at a paragraph boundary.
     *
     * @var string
     */
    protected const NOT_AT_PARA_BREAK = '(?!' . Lexer::PARA_BREAK . ')';

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
     *
     * @var string
     */
    protected const NON_WORD_CHAR = '[\s!"#$%&\'()*+,\-./:;<=>?@\[\\\\\]^`{|}~]';

    /**
     * Zero-width assertion: current position is preceded by a non-word
     * character, or is at the start of input/line. See {@see self::NON_WORD_CHAR}
     * for the multibyte reasoning.
     *
     * @var string
     */
    protected const NO_WORD_BEFORE = '(?:^|(?<=' . self::NON_WORD_CHAR . '))';

    /**
     * Zero-width assertion: current position is followed by a non-word
     * character, or is at the end of input. Complement to
     * {@see self::NO_WORD_BEFORE}.
     *
     * @var string
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
     * modes included), merged with any names the subclass assigned to
     * $allowedModes directly, then passed through filterAllowedModes(). A subclass
     * that does not use categories has its directly-assigned $allowedModes used
     * as-is.
     *
     * @param ModeRegistry $registry
     * @return void
     */
    public function setModeRegistry(ModeRegistry $registry): void
    {
        $this->registry = $registry;

        $modes = (array) $this->allowedModes;
        $categories = $this->allowedCategories();
        if ($categories) {
            $modes = array_merge($modes, $registry->getModesForCategories($categories));
        }
        $this->allowedModes = $this->filterAllowedModes(array_values(array_unique($modes)));
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
