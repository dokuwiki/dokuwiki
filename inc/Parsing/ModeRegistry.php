<?php

namespace dokuwiki\Parsing;

use dokuwiki\Extension\PluginInterface;
use dokuwiki\Extension\SyntaxPlugin;
use dokuwiki\Parsing\ParserMode\Acronym;
use dokuwiki\Parsing\ParserMode\ModeInterface;
use dokuwiki\Parsing\ParserMode\Camelcaselink;
use dokuwiki\Parsing\ParserMode\Entity;
use dokuwiki\Parsing\ParserMode\Smiley;

/**
 * Central registry for parser mode categories and mode instantiation.
 *
 * The underlying data is kept in the global $PARSER_MODES array because
 * third-party plugins read and write it directly at runtime (e.g. to register
 * their mode in a category). All methods in this class operate on that global
 * so changes are visible to both old and new code.
 */
class ModeRegistry
{
    // Category constants (preserving the historical 'substition' typo)
    public const CATEGORY_CONTAINER   = 'container';
    public const CATEGORY_BASEONLY    = 'baseonly';
    public const CATEGORY_FORMATTING  = 'formatting';
    public const CATEGORY_SUBSTITION  = 'substition';
    public const CATEGORY_PROTECTED   = 'protected';
    public const CATEGORY_DISABLED    = 'disabled';
    public const CATEGORY_PARAGRAPHS  = 'paragraphs';

    /** @var array{sort: int, mode: string, obj: ModeInterface}[]|null */
    private ?array $modes = null;

    /** @var string[] Modes that handle their own line endings (skip EOL connection) */
    private array $blockEolModes = [];

    private static ?self $instance = null;

    /**
     * Get the singleton instance of the ModeRegistry.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Reset the singleton instance.
     *
     * This is mainly useful for testing to force re-initialization.
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$instance = null;
    }

    /**
     * Constructor. Initializes the global $PARSER_MODES array with the default mode categories.
     */
    private function __construct()
    {
        global $PARSER_MODES;
        $PARSER_MODES = [
            self::CATEGORY_CONTAINER  => ['listblock', 'table', 'quote', 'hr'],
            self::CATEGORY_BASEONLY   => ['header', 'gfm_header'],
            self::CATEGORY_FORMATTING => [
                'strong', 'emphasis', 'underline', 'monospace',
                'subscript', 'superscript', 'deleted', 'footnote',
                'gfm_emphasis', 'gfm_emphasis_underscore', 'gfm_strong_underscore',
                'gfm_emphasis_strong', 'gfm_emphasis_strong_underscore',
                'gfm_deleted', 'gfm_backtick_single', 'gfm_backtick_double',
            ],
            self::CATEGORY_SUBSTITION => [
                'acronym', 'smiley', 'wordblock', 'entity',
                'camelcaselink', 'internallink', 'media', 'externallink',
                'linebreak', 'emaillink', 'windowssharelink', 'filelink',
                'notoc', 'nocache', 'multiplyentity', 'quotes', 'rss',
                'gfm_link', 'gfm_media',
            ],
            self::CATEGORY_PROTECTED  => ['preformatted', 'code', 'file', 'gfm_code', 'gfm_file'],
            self::CATEGORY_DISABLED   => ['unformatted'],
            self::CATEGORY_PARAGRAPHS => ['eol'],
        ];
    }

    /**
     * Get all mode names in the given categories.
     *
     * @param string[] $categories One or more CATEGORY_* constants
     * @return string[] Unique list of mode names
     */
    public function getModesForCategories(array $categories): array
    {
        global $PARSER_MODES;
        $modes = [];
        foreach ($categories as $cat) {
            if (isset($PARSER_MODES[$cat])) {
                $modes = array_merge($modes, $PARSER_MODES[$cat]);
            }
        }
        return array_unique($modes);
    }

    /**
     * Get the raw categories array.
     *
     * @return array<string, string[]> Category name => list of mode names
     */
    public function getCategories(): array
    {
        global $PARSER_MODES;
        return $PARSER_MODES;
    }

    /**
     * Register a mode in a category.
     *
     * @param string $category One of the CATEGORY_* constants
     * @param string $modeName The mode name to register
     * @return void
     */
    public function registerMode(string $category, string $modeName): void
    {
        global $PARSER_MODES;
        $PARSER_MODES[$category][] = $modeName;
        $this->modes = null; // invalidate cached mode list
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
     * Get all parser modes, fully instantiated and sorted by priority.
     *
     * This includes syntax plugins, built-in modes, formatting modes, and
     * data-driven modes (smileys, acronyms, entities). Results are cached
     * unless running in a test environment.
     *
     * @return array[] Each entry is ['sort' => int, 'mode' => string, 'obj' => ModeInterface]
     */
    public function getModes(): array
    {
        global $conf;

        if ($this->modes !== null && !defined('DOKU_UNITTEST')) {
            return $this->modes;
        }

        $this->modes = [];
        $syntax = $conf['syntax'] ?? 'dokuwiki';
        $loadDw = in_array($syntax, ['dokuwiki', 'dw+md', 'md+dw']);
        $loadMd = in_array($syntax, ['markdown', 'dw+md', 'md+dw']);

        $this->loadPluginModes();
        $this->loadAlwaysModes();
        if ($loadDw) $this->loadDokuWikiModes();
        if ($loadMd) $this->loadMarkdownModes();
        $this->loadDataModes();

        usort($this->modes, self::sortModes(...));
        return $this->modes;
    }

    /**
     * Load syntax plugin modes and register them in their categories.
     */
    protected function loadPluginModes(): void
    {
        global $PARSER_MODES;

        $plugins = plugin_list('syntax');
        foreach ($plugins as $p) {
            $obj = plugin_load('syntax', $p);
            if (!$obj instanceof PluginInterface) continue;
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
            'quote', 'externallink', 'emaillink', 'windowssharelink',
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
     * Skipped when syntax is 'markdown'.
     */
    protected function loadDokuWikiModes(): void
    {
        global $conf;
        $syntax = $conf['syntax'] ?? 'dokuwiki';
        $dwPreferred = in_array($syntax, ['dokuwiki', 'dw+md'], true);

        $modes = [
            'emphasis', 'deleted', 'code', 'header', 'hr',
            'linebreak', 'internallink', 'media', 'listblock', 'table',
            'monospace', 'unformatted', 'file',
        ];

        // Underline only loads when DokuWiki is preferred. In MD-preferred
        // modes, `__` means strong (via gfm_strong_underscore) and loading
        // Underline here would conflict.
        if ($dwPreferred) {
            $modes[] = 'underline';
        }

        $this->instantiateModes($modes);
    }

    /**
     * Load Markdown-specific modes for features that also exist in DokuWiki.
     * Skipped when syntax is 'dokuwiki'.
     */
    protected function loadMarkdownModes(): void
    {
        global $conf;
        $syntax = $conf['syntax'] ?? 'dokuwiki';
        $mdPreferred = in_array($syntax, ['markdown', 'md+dw'], true);

        $modes = [
            'gfm_emphasis', 'gfm_emphasis_strong', 'gfm_deleted',
            'gfm_backtick_single', 'gfm_backtick_double',
            'gfm_header', 'gfm_link', 'gfm_media',
            'gfm_code', 'gfm_file',
        ];

        // Underscore-based emphasis and strong only load when Markdown is
        // preferred. In DW-preferred modes, `__` means underline and loading
        // these would conflict.
        if ($mdPreferred) {
            $modes[] = 'gfm_emphasis_underscore';
            $modes[] = 'gfm_strong_underscore';
            $modes[] = 'gfm_emphasis_strong_underscore';
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
            $class = implode('', array_map('ucfirst', explode('_', $mode))); // snake_case to PascalCase
            $class = 'dokuwiki\\Parsing\\ParserMode\\' . $class; // prepend namespace
            $obj = new $class();
            $this->modes[] = [
                'sort' => $obj->getSort(),
                'mode' => $mode,
                'obj'  => $obj,
            ];
        }
    }

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
