<?php

namespace dokuwiki\Parsing;

use dokuwiki\Extension\PluginInterface;
use dokuwiki\Extension\SyntaxPlugin;
use dokuwiki\Parsing\ParserMode\Acronym;
use dokuwiki\Parsing\ParserMode\AbstractMode;
use dokuwiki\Parsing\ParserMode\Camelcaselink;
use dokuwiki\Parsing\ParserMode\Entity;
use dokuwiki\Parsing\ParserMode\Smiley;
use dokuwiki\Parsing\Handler;
use dokuwiki\Parsing\Parser;

/**
 * The set of parser modes for a single parse, plus the mode taxonomy.
 *
 * A ModeRegistry is built once per parse (see p_get_instructions) and
 * carries the parse-specific state: the active syntax flavour, the
 * block-EOL bookkeeping, and the sub-parser pool. It is a short-lived
 * value, not a singleton — two parses in the same request (e.g. a plugin
 * rendering bundled DW text inside an otherwise-Markdown page) get two
 * independent registries.
 *
 * Three distinct concepts meet here; keep them apart:
 *
 *   1. The user's configured syntax PREFERENCE is a setting. Its source
 *      of truth is $conf['syntax']. Read it only in UI code (editor
 *      toolbar, admin settings, syntax-preference plugins) — never from
 *      inside the parser. $conf['syntax'] enters the parser exactly once,
 *      at the top-level entry point, as this registry's constructor
 *      argument.
 *
 *   2. The active parse's syntax is a PARAMETER of this registry
 *      (getSyntax / isDwPreferred / isMdPreferred). Every mode descends from
 *      AbstractMode, which Parser::addMode() injects this registry into, so a
 *      mode reads it via $this->registry; a plugin handle()/render() reads
 *      $handler->getModeRegistry(). No code inside inc/Parsing/ reads
 *      $conf['syntax'] directly.
 *
 *   3. The mode TAXONOMY — which mode names belong to which category — is
 *      owned by this registry instance ($this->categories), seeded from the
 *      immutable DEFAULT_CATEGORIES and extended with plugin_* entries during
 *      loadPluginModes(). Core reads it through the instance accessors
 *      (getModesForCategories / getCategories). The legacy global
 *      $PARSER_MODES is kept only as a deprecated mirror, published during
 *      loadPluginModes() for third-party plugins that read the array directly
 *      and for the bundled info plugin — no core code reads it.
 */
class ModeRegistry
{
    // Category constants (preserving the historical 'substition' typo)
    public const CATEGORY_CONTAINER    = 'container';
    public const CATEGORY_BASEONLY     = 'baseonly';
    public const CATEGORY_FORMATTING   = 'formatting';
    public const CATEGORY_SUBSTITUTION = 'substition';
    public const CATEGORY_PROTECTED    = 'protected';
    public const CATEGORY_DISABLED     = 'disabled';
    public const CATEGORY_PARAGRAPHS   = 'paragraphs';

    /**
     * The built-in mode taxonomy: category => list of mode names.
     *
     * Immutable defaults. Each registry starts from a copy of this in
     * $this->categories; loadPluginModes() then merges plugin_* entries into
     * that copy. Being a const, it is never mutated and so needs no resetting
     * between parses or tests.
     */
    protected const DEFAULT_CATEGORIES = [
        self::CATEGORY_CONTAINER  => ['listblock', 'table', 'gfm_listblock', 'gfm_table', 'gfm_quote', 'gfm_hr'],
        self::CATEGORY_BASEONLY   => ['header', 'gfm_header'],
        self::CATEGORY_FORMATTING => [
            'strong', 'emphasis', 'underline', 'monospace',
            'subscript', 'superscript', 'deleted', 'footnote',
            'gfm_emphasis', 'gfm_emphasis_underscore', 'gfm_strong_underscore',
            'gfm_emphasis_strong', 'gfm_emphasis_strong_underscore',
            'gfm_deleted', 'gfm_backtick_single', 'gfm_backtick_double',
        ],
        self::CATEGORY_SUBSTITUTION => [
            'acronym', 'smiley', 'wordblock', 'entity',
            'camelcaselink', 'internallink', 'media', 'externallink',
            'linebreak', 'emaillink', 'windowssharelink', 'filelink',
            'notoc', 'nocache', 'multiplyentity', 'quotes', 'rss',
            'gfm_link', 'gfm_media', 'gfm_escape', 'gfm_linebreak',
            'gfm_html_entity',
        ],
        self::CATEGORY_PROTECTED  => ['preformatted', 'code', 'file', 'gfm_code', 'gfm_file'],
        self::CATEGORY_DISABLED   => ['unformatted'],
        self::CATEGORY_PARAGRAPHS => ['eol'],
    ];

    /** @var array{sort: int, mode: string, obj: AbstractMode}[]|null */
    protected ?array $modes = null;

    /** @var array<string, array{parsers: Parser[], inUse: int}> Pool of sub-parsers per exclusion-set identifier. */
    protected array $subParsers = [];

    /** @var string[] Modes that handle their own line endings (skip EOL connection) */
    protected array $blockEolModes = [];

    /** @var string the syntax flavour this parse runs under (dw, md, dw+md, md+dw) */
    protected string $syntax;

    /** @var array<string, string[]> this parse's mode taxonomy (defaults + plugin modes) */
    protected array $categories = self::DEFAULT_CATEGORIES;

    /**
     * @param string $syntax the syntax flavour for this parse: one of
     *     'dw', 'md', 'dw+md', 'md+dw'. This is the active-parse parameter,
     *     not the user preference — see the class docblock.
     */
    public function __construct(string $syntax)
    {
        $this->syntax = $syntax;
    }

    /**
     * The syntax flavour of this parse.
     *
     * @return string one of 'dw', 'md', 'dw+md', 'md+dw'
     */
    public function getSyntax(): string
    {
        return $this->syntax;
    }

    /**
     * Get all mode names in the given categories of this parse's taxonomy.
     *
     * @param string[] $categories One or more CATEGORY_* constants
     * @return string[] Unique list of mode names
     */
    public function getModesForCategories(array $categories): array
    {
        $modes = [];
        foreach ($categories as $cat) {
            if (isset($this->categories[$cat])) {
                $modes = array_merge($modes, $this->categories[$cat]);
            }
        }
        return array_unique($modes);
    }

    /**
     * Get this parse's raw category map.
     *
     * Returns the taxonomy as loaded so far. The built-in categories are
     * present from construction, but the plugin_* mode names are only merged in
     * once getModes() has run its plugin loading. Call getModes() first if you
     * need the complete taxonomy including syntax plugins.
     *
     * @return array<string, string[]> Category name => list of mode names
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * Register a mode in a category of this parse's taxonomy.
     *
     * @param string $category One of the CATEGORY_* constants
     * @param string $modeName The mode name to register
     * @return void
     */
    public function registerMode(string $category, string $modeName): void
    {
        $this->categories[$category][] = $modeName;
    }

    /**
     * Register a mode that handles its own line endings.
     * Modes registered here will be skipped by Eol's connectTo().
     *
     * @param string $mode The mode name
     * @return void
     */
    public function registerBlockEolMode(string $mode): void
    {
        $this->blockEolModes[] = $mode;
    }

    /**
     * Get all modes that handle their own line endings.
     *
     * @return string[]
     */
    public function getBlockEolModes(): array
    {
        return $this->blockEolModes;
    }

    /**
     * Whether DokuWiki is the preferred syntax (`dw` or `dw+md`).
     *
     * Modes that have to choose between DW-flavored and MD-flavored
     * behavior at runtime read this flag. Compare with isMdPreferred()
     * — exactly one of the two is true for any valid `$conf['syntax']`
     * setting.
     */
    public function isDwPreferred(): bool
    {
        return in_array($this->syntax, ['dw', 'dw+md'], true);
    }

    /**
     * Whether Markdown is the preferred syntax (`md` or `md+dw`).
     */
    public function isMdPreferred(): bool
    {
        return in_array($this->syntax, ['md', 'md+dw'], true);
    }

    /**
     * Get all parser modes, fully instantiated and sorted by priority.
     *
     * This includes syntax plugins, built-in modes, formatting modes, and
     * data-driven modes (smileys, acronyms, entities). Built once per
     * registry and memoised for that registry's (short) lifetime.
     *
     * @return array[] Each entry is ['sort' => int, 'mode' => string, 'obj' => AbstractMode]
     */
    public function getModes(): array
    {
        if ($this->modes !== null) {
            return $this->modes;
        }

        $this->modes = [];
        $loadDw = in_array($this->syntax, ['dw', 'dw+md', 'md+dw'], true);
        $loadMd = in_array($this->syntax, ['md', 'dw+md', 'md+dw'], true);

        $this->loadPluginModes();
        $this->loadAlwaysModes();
        if ($loadDw) $this->loadDokuWikiModes();
        if ($loadMd) $this->loadMarkdownModes();
        $this->loadDataModes();

        usort($this->modes, self::sortModes(...));
        return $this->modes;
    }

    //region Sub-parser pool

    /**
     * Acquire a sub-parser for the given exclusion set.
     *
     * The registry maintains a pool of sub-parsers per exclusion key.
     * Each acquire returns the next free instance from that pool;
     * releaseSubParser must be called (with the same exclusion set)
     * once the caller is done. If all instances in a pool are already
     * checked out — re-entrancy on the same key — a fresh instance is
     * built and appended to the pool. Real-world nesting for any one
     * mode tops out at a handful of levels, so pool growth is bounded.
     *
     * Use this primitive when the caller wants to hold the parser
     * across multiple parse() calls (e.g. iterating over list items).
     * For single-shot use, prefer {@see withSubParser} so release is
     * automatic.
     *
     * The returned Parser is shared infrastructure: callers must call
     * `$parser->getHandler()->reset()` before each parse() to avoid
     * inheriting state from a previous use.
     *
     * @param string[] $excludeCategories CATEGORY_* constants whose modes should be excluded
     * @param string[] $excludeModes specific mode names to exclude in addition to category-based exclusions
     */
    public function acquireSubParser(
        array $excludeCategories = [self::CATEGORY_BASEONLY],
        array $excludeModes = []
    ): Parser {
        $key = $this->subParserKey($excludeCategories, $excludeModes);
        $entry = $this->subParsers[$key] ?? ['parsers' => [], 'inUse' => 0];

        if ($entry['inUse'] >= count($entry['parsers'])) {
            $entry['parsers'][] = $this->buildSubParser($excludeCategories, $excludeModes);
        }
        $parser = $entry['parsers'][$entry['inUse']];
        $entry['inUse']++;
        $this->subParsers[$key] = $entry;
        return $parser;
    }

    /**
     * Release a previously-acquired sub-parser back to its pool.
     *
     * Should be paired with a prior {@see acquireSubParser} call for
     * the same exclusion set. Callers must release in LIFO order with
     * respect to other acquires on the same key — the implementation
     * does not enforce LIFO, but out-of-order release would silently
     * hand the same parser to two callers, so the caller is responsible
     * for the discipline. Wrapping each acquire/release pair in a
     * single try/finally (or using {@see withSubParser}) makes the
     * ordering correct by construction.
     *
     * Throws if no acquire is outstanding for the given key — that
     * indicates an acquire/release imbalance bug in the caller.
     *
     * @param string[] $excludeCategories
     * @param string[] $excludeModes
     * @throws \RuntimeException on release without a matching acquire
     */
    public function releaseSubParser(
        array $excludeCategories = [self::CATEGORY_BASEONLY],
        array $excludeModes = []
    ): void {
        $key = $this->subParserKey($excludeCategories, $excludeModes);
        if (!isset($this->subParsers[$key]) || $this->subParsers[$key]['inUse'] <= 0) {
            throw new \RuntimeException(
                "releaseSubParser called without matching acquireSubParser for key '$key'"
            );
        }
        $this->subParsers[$key]['inUse']--;
    }

    /**
     * Run a callback with an exclusively-held sub-parser.
     *
     * Convenience wrapper around acquire/release. The parser is checked
     * out for the duration of the callback, then released even if the
     * callback throws. Preferred shape for single-shot sub-parses
     * (one parse() call per acquire); use the explicit pair for cases
     * where the parser is held across a loop or other longer scope.
     *
     * @template T
     * @param string[] $excludeCategories
     * @param string[] $excludeModes
     * @param callable(Parser): T $fn
     * @return T
     */
    public function withSubParser(
        array $excludeCategories,
        array $excludeModes,
        callable $fn
    ) {
        $parser = $this->acquireSubParser($excludeCategories, $excludeModes);
        try {
            return $fn($parser);
        } finally {
            $this->releaseSubParser($excludeCategories, $excludeModes);
        }
    }

    /**
     * Build a fresh Parser preconfigured with every active mode except
     * the ones excluded.
     *
     * Mode objects are cloned before being attached so that
     * Parser::addMode() pointing each mode at the sub-parser's lexer does not
     * clobber the main parser's mode references.
     *
     * @param string[] $excludeCategories
     * @param string[] $excludeModes
     */
    protected function buildSubParser(
        array $excludeCategories,
        array $excludeModes
    ): Parser {
        $categories = $this->getCategories();
        $excluded = $excludeModes;
        foreach ($excludeCategories as $cat) {
            $excluded = array_merge($excluded, $categories[$cat] ?? []);
        }

        $parser = new Parser(new Handler($this, true), $this);
        foreach ($this->getModes() as $m) {
            if (in_array($m['mode'], $excluded, true)) continue;
            // Mode objects expose a single $Lexer slot which Parser::addMode()
            // overwrites at registration time. The objects in $this->modes are
            // already attached to the main parser's lexer; reusing them here
            // would clobber that reference and break the main parse. Clone so
            // the sub-parser gets its own copy with its own $Lexer slot.
            $parser->addMode($m['mode'], clone $m['obj']);
        }
        return $parser;
    }

    /**
     * Build the cache key used to identify a sub-parser exclusion set.
     */
    protected function subParserKey(array $excludeCategories, array $excludeModes): string
    {
        return implode(',', $excludeCategories) . '|' . implode(',', $excludeModes);
    }

    //endregion

    //region Mode loading

    /**
     * Load syntax plugin modes and register them in their categories.
     */
    protected function loadPluginModes(): void
    {
        global $PARSER_MODES;

        // Publish this parse's taxonomy into the deprecated global mirror right
        // before plugins load — third-party plugins read $PARSER_MODES directly
        // (often from their constructor) and the info plugin reads it at render.
        // Core never reads the mirror; it reads $this->categories. The mirror is
        // kept in sync incrementally below so a plugin loaded later sees the
        // modes registered by plugins loaded before it (historical behaviour).
        // @deprecated reading $PARSER_MODES directly — use the ModeRegistry API.
        $PARSER_MODES = $this->categories;

        $plugins = plugin_list('syntax');
        foreach ($plugins as $p) {
            $obj = plugin_load('syntax', $p);
            if (!$obj instanceof PluginInterface) continue;
            $this->categories[$obj->getType()][] = "plugin_$p";
            $PARSER_MODES[$obj->getType()][] = "plugin_$p";
            $this->modes[] = [
                'sort' => $obj->getSort(),
                'mode' => "plugin_$p",
                'obj'  => $obj,
            ];
            unset($obj);
        }
    }

    /**
     * Load modes that have no equivalent in the other syntax.
     * These are always active regardless of the syntax setting.
     */
    protected function loadAlwaysModes(): void
    {
        global $conf;

        $modes = [
            'strong', 'subscript', 'superscript',
            'footnote', 'eol', 'preformatted',
            'gfm_quote', 'gfm_hr',
            'externallink', 'emaillink', 'windowssharelink',
            'notoc', 'nocache', 'rss',
        ];

        if ($conf['typography']) {
            $modes[] = 'quotes';
            $modes[] = 'multiplyentity';
        }

        $this->instantiateModes($modes);
    }

    /**
     * Load DokuWiki-specific modes for features that also exist in Markdown.
     * Skipped when syntax is 'md'.
     */
    protected function loadDokuWikiModes(): void
    {
        $modes = [
            'emphasis', 'deleted', 'code', 'header',
            'linebreak', 'internallink', 'media', 'table',
            'monospace', 'unformatted', 'file',
        ];

        // Underline only loads when DokuWiki is preferred. In MD-preferred
        // modes, `__` means strong (via gfm_strong_underscore) and loading
        // Underline here would conflict.
        //
        // Listblock only loads when DokuWiki is preferred. In MD-preferred
        // modes, GfmListblock owns the `-`/`*`/`+` markers and zero-indent
        // top-level items, which conflicts with DokuWiki's required-2-space-
        // indent list model.
        if ($this->isDwPreferred()) {
            $modes[] = 'underline';
            $modes[] = 'listblock';
        }

        $this->instantiateModes($modes);
    }

    /**
     * Load Markdown-specific modes for features that also exist in DokuWiki.
     * Skipped when syntax is 'dw'.
     */
    protected function loadMarkdownModes(): void
    {
        $modes = [
            'gfm_escape', 'gfm_linebreak', 'gfm_html_entity',
            'gfm_emphasis', 'gfm_emphasis_strong', 'gfm_deleted',
            'gfm_backtick_single', 'gfm_backtick_double',
            'gfm_header', 'gfm_link', 'gfm_media',
            'gfm_code', 'gfm_file', 'gfm_table',
        ];

        // Underscore-based emphasis and strong only load when Markdown is
        // preferred. In DW-preferred modes, `__` means underline and loading
        // these would conflict.
        //
        // GfmListblock only loads when Markdown is preferred. In DW-preferred
        // modes, the DokuWiki Listblock owns the `-`/`*` markers (with the
        // 2-space indent rule); the two list models cannot co-exist.
        if ($this->isMdPreferred()) {
            $modes[] = 'gfm_emphasis_underscore';
            $modes[] = 'gfm_strong_underscore';
            $modes[] = 'gfm_emphasis_strong_underscore';
            $modes[] = 'gfm_listblock';
        }

        $this->instantiateModes($modes);
    }

    /**
     * Load data-driven modes that require constructor arguments
     * (smileys, acronyms, entities) and optional config-gated modes.
     */
    protected function loadDataModes(): void
    {
        global $conf;

        $obj = new Smiley(array_keys(getSmileys()));
        $this->modes[] = ['sort' => $obj->getSort(), 'mode' => 'smiley', 'obj' => $obj];

        $obj = new Acronym(array_keys(getAcronyms()));
        $this->modes[] = ['sort' => $obj->getSort(), 'mode' => 'acronym', 'obj' => $obj];

        $obj = new Entity(array_keys(getEntities()));
        $this->modes[] = ['sort' => $obj->getSort(), 'mode' => 'entity', 'obj' => $obj];

        if (!empty($conf['camelcase'])) {
            $obj = new Camelcaselink();
            $this->modes[] = ['sort' => $obj->getSort(), 'mode' => 'camelcaselink', 'obj' => $obj];
        }
    }

    /**
     * Instantiate mode classes by name and add them to the mode list.
     *
     * Mode names are split on `_` and each segment is PascalCased to form the
     * class name (e.g. `gfm_emphasis_underscore` → `GfmEmphasisUnderscore`,
     * `internallink` → `Internallink`, `strong` → `Strong`).
     *
     * @param string[] $modeNames
     */
    protected function instantiateModes(array $modeNames): void
    {
        foreach ($modeNames as $mode) {
            $class = implode('', array_map(ucfirst(...), explode('_', $mode))); // snake_case to PascalCase
            $class = 'dokuwiki\\Parsing\\ParserMode\\' . $class; // prepend namespace
            $obj = new $class();
            $this->modes[] = [
                'sort' => $obj->getSort(),
                'mode' => $mode,
                'obj'  => $obj,
            ];
        }
    }

    //endregion

    /**
     * Callback function for usort
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    public static function sortModes(array $a, array $b): int
    {
        return $a['sort'] <=> $b['sort'];
    }
}
