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
            self::CATEGORY_BASEONLY   => ['header'],
            self::CATEGORY_FORMATTING => [
                'strong', 'emphasis', 'underline', 'monospace',
                'subscript', 'superscript', 'deleted', 'footnote',
            ],
            self::CATEGORY_SUBSTITION => [
                'acronym', 'smiley', 'wordblock', 'entity',
                'camelcaselink', 'internallink', 'media', 'externallink',
                'linebreak', 'emaillink', 'windowssharelink', 'filelink',
                'notoc', 'nocache', 'multiplyentity', 'quotes', 'rss',
            ],
            self::CATEGORY_PROTECTED  => ['preformatted', 'code', 'file'],
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

        global $PARSER_MODES;
        $this->modes = [];

        // 1. Load syntax plugins and register their modes
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

        // 2. Add standard built-in modes
        $builtinModes = [
            'listblock', 'preformatted', 'notoc', 'nocache',
            'header', 'table', 'linebreak', 'footnote',
            'hr', 'unformatted', 'code', 'file', 'quote',
            'internallink', 'rss', 'media', 'externallink',
            'emaillink', 'windowssharelink', 'eol',
            'strong', 'emphasis', 'underline', 'monospace',
            'subscript', 'superscript', 'deleted',
        ];
        if ($conf['typography']) {
            $builtinModes[] = 'quotes';
            $builtinModes[] = 'multiplyentity';
        }
        foreach ($builtinModes as $mode) {
            $class = 'dokuwiki\\Parsing\\ParserMode\\' . ucfirst($mode);
            $obj = new $class();
            $this->modes[] = [
                'sort' => $obj->getSort(),
                'mode' => $mode,
                'obj'  => $obj,
            ];
        }

        // 3. Add data-driven modes
        $obj = new Smiley(array_keys(getSmileys()));
        $this->modes[] = ['sort' => $obj->getSort(), 'mode' => 'smiley', 'obj' => $obj];

        $obj = new Acronym(array_keys(getAcronyms()));
        $this->modes[] = ['sort' => $obj->getSort(), 'mode' => 'acronym', 'obj' => $obj];

        $obj = new Entity(array_keys(getEntities()));
        $this->modes[] = ['sort' => $obj->getSort(), 'mode' => 'entity', 'obj' => $obj];

        // 4. Optional camelcase mode
        if (!empty($conf['camelcase'])) {
            $obj = new Camelcaselink();
            $this->modes[] = ['sort' => $obj->getSort(), 'mode' => 'camelcaselink', 'obj' => $obj];
        }

        // 5. Sort by priority
        usort($this->modes, self::sortModes(...));

        return $this->modes;
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
