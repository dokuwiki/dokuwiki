<?php

namespace dokuwiki\test\Parsing;

use dokuwiki\Parsing\Handler;
use dokuwiki\Parsing\ModeRegistry;
use dokuwiki\Parsing\Parser;
use dokuwiki\Parsing\ParserMode\AbstractMode;

class ModeRegistryTest extends \DokuWikiTest
{
    /** @var ModeRegistry */
    private $registry;

    function setUp(): void
    {
        parent::setUp();
        global $conf;
        $this->registry = new ModeRegistry($conf['syntax']);
    }

    function testGetModesPublishesGlobalMirror()
    {
        // The deprecated global mirror is published when the mode list is built.
        global $PARSER_MODES;
        (new ModeRegistry('dw'))->getModes();
        $this->assertIsArray($PARSER_MODES);
        $this->assertArrayHasKey('container', $PARSER_MODES);
        $this->assertArrayHasKey('formatting', $PARSER_MODES);
        $this->assertArrayHasKey('substition', $PARSER_MODES);
        $this->assertArrayHasKey('protected', $PARSER_MODES);
        $this->assertArrayHasKey('disabled', $PARSER_MODES);
        $this->assertArrayHasKey('paragraphs', $PARSER_MODES);
        $this->assertArrayHasKey('baseonly', $PARSER_MODES);
    }

    function testGlobalMirrorMatchesInstanceTaxonomy()
    {
        // After the mode list is built, the global mirror equals the instance's
        // taxonomy (defaults + plugin modes).
        global $PARSER_MODES;
        $registry = new ModeRegistry('dw');
        $registry->getModes();
        $this->assertSame($registry->getCategories(), $PARSER_MODES);
    }

    function testGetSyntaxReturnsConstructorArgument()
    {
        $this->assertSame('md+dw', (new ModeRegistry('md+dw'))->getSyntax());
    }

    function testGetCategories()
    {
        $cats = $this->registry->getCategories();
        $this->assertArrayHasKey('container', $cats);
        $this->assertArrayHasKey('formatting', $cats);
        $this->assertArrayHasKey('baseonly', $cats);
    }

    function testGetModesForSingleCategory()
    {
        $modes = $this->registry->getModesForCategories([ModeRegistry::CATEGORY_CONTAINER]);
        $this->assertContains('listblock', $modes);
        $this->assertContains('table', $modes);
        $this->assertContains('gfm_quote', $modes);
        $this->assertContains('gfm_hr', $modes);
    }

    function testGetModesForMultipleCategories()
    {
        $modes = $this->registry->getModesForCategories([
            ModeRegistry::CATEGORY_CONTAINER,
            ModeRegistry::CATEGORY_BASEONLY,
        ]);
        $this->assertContains('listblock', $modes);
        $this->assertContains('header', $modes);
    }

    function testGetModesForCategoriesDeduplicates()
    {
        $modes = $this->registry->getModesForCategories([
            ModeRegistry::CATEGORY_CONTAINER,
            ModeRegistry::CATEGORY_CONTAINER,
        ]);
        $counts = array_count_values($modes);
        foreach ($counts as $count) {
            $this->assertEquals(1, $count);
        }
    }

    function testGetModesForUnknownCategoryReturnsEmpty()
    {
        $modes = $this->registry->getModesForCategories(['nonexistent']);
        $this->assertSame([], $modes);
    }

    function testRegisterMode()
    {
        $this->registry->registerMode(ModeRegistry::CATEGORY_CONTAINER, 'testmode');
        $this->assertContains(
            'testmode',
            $this->registry->getModesForCategories([ModeRegistry::CATEGORY_CONTAINER])
        );
    }

    function testRegisterModeIsPerInstance()
    {
        // Registering on one registry must not leak into another.
        $this->registry->registerMode(ModeRegistry::CATEGORY_CONTAINER, 'leaktest');
        $other = new ModeRegistry('dw');
        $this->assertNotContains(
            'leaktest',
            $other->getModesForCategories([ModeRegistry::CATEGORY_CONTAINER])
        );
    }

    function testGetModesReturnsSortedArray()
    {
        $modes = $this->registry->getModes();
        $this->assertNotEmpty($modes);

        $sortValues = array_column($modes, 'sort');
        $sorted = $sortValues;
        sort($sorted);
        $this->assertSame($sorted, $sortValues);
    }

    function testGetModesContainsExpectedKeys()
    {
        $modes = $this->registry->getModes();
        foreach ($modes as $entry) {
            $this->assertArrayHasKey('sort', $entry);
            $this->assertArrayHasKey('mode', $entry);
            $this->assertArrayHasKey('obj', $entry);
            $this->assertIsInt($entry['sort']);
            $this->assertIsString($entry['mode']);
            $this->assertInstanceOf(AbstractMode::class, $entry['obj']);
        }
    }

    function testGetModesContainsBuiltinModes()
    {
        $modes = (new ModeRegistry('dw'))->getModes();
        $modeNames = array_column($modes, 'mode');
        $this->assertContains('strong', $modeNames);
        $this->assertContains('header', $modeNames);
        $this->assertContains('listblock', $modeNames);
        $this->assertContains('eol', $modeNames);
        $this->assertContains('smiley', $modeNames);
        $this->assertContains('acronym', $modeNames);
        $this->assertContains('entity', $modeNames);
    }

    function testSortModes()
    {
        $a = ['sort' => 10, 'mode' => 'a'];
        $b = ['sort' => 20, 'mode' => 'b'];
        $this->assertLessThan(0, ModeRegistry::sortModes($a, $b));
        $this->assertGreaterThan(0, ModeRegistry::sortModes($b, $a));
        $this->assertEquals(0, ModeRegistry::sortModes($a, $a));
    }

    function testBlockEolModesEmptyByDefault()
    {
        $this->assertSame([], $this->registry->getBlockEolModes());
    }

    function testRegisterBlockEolMode()
    {
        $this->registry->registerBlockEolMode('listblock');
        $this->registry->registerBlockEolMode('table');
        $this->assertSame(['listblock', 'table'], $this->registry->getBlockEolModes());
    }

    function testBlockEolModesArePerRegistry()
    {
        $this->registry->registerBlockEolMode('listblock');
        $fresh = new ModeRegistry('dw');
        $this->assertSame([], $fresh->getBlockEolModes());
    }

    /**
     * The default syntax setting must produce the exact same mode set as before
     * the syntax setting was introduced (no-op guarantee).
     */
    function testGetModesDefaultSyntaxMatchesLegacy()
    {
        $modes = (new ModeRegistry('dw'))->getModes();
        $modeNames = array_column($modes, 'mode');

        // All original built-in modes must be present (with `quote`
        // and `hr` replaced by the unified `gfm_quote` and `gfm_hr`
        // that cover both DW and GFM dialects).
        $expected = [
            'listblock', 'preformatted', 'notoc', 'nocache',
            'header', 'table', 'linebreak', 'footnote',
            'gfm_hr', 'unformatted', 'code', 'file', 'gfm_quote',
            'internallink', 'rss', 'media', 'externallink',
            'emaillink', 'windowssharelink', 'eol',
            'strong', 'emphasis', 'underline', 'monospace',
            'subscript', 'superscript', 'deleted',
            'smiley', 'acronym', 'entity',
        ];
        foreach ($expected as $mode) {
            $this->assertContains($mode, $modeNames, "Mode '$mode' missing in dw syntax setting");
        }
    }

    /** DW-only modes must be absent when syntax is 'md' */
    function testGetModesDwModesSkippedInMarkdownOnly()
    {
        $modes = (new ModeRegistry('md'))->getModes();
        $modeNames = array_column($modes, 'mode');

        $dwOnly = [
            'emphasis', 'deleted', 'code', 'header',
            'linebreak', 'internallink', 'media', 'listblock', 'table',
            'monospace', 'unformatted', 'file',
        ];
        foreach ($dwOnly as $mode) {
            $this->assertNotContains($mode, $modeNames, "DW mode '$mode' should not load in md-only mode");
        }
    }

    /** Always-loaded modes must still be present in md-only mode */
    function testGetModesAlwaysModesPresentInMarkdownOnly()
    {
        $modes = (new ModeRegistry('md'))->getModes();
        $modeNames = array_column($modes, 'mode');

        $always = [
            'strong', 'subscript', 'superscript',
            'footnote', 'eol', 'preformatted',
            'gfm_quote', 'gfm_hr', 'externallink', 'emaillink', 'windowssharelink',
            'notoc', 'nocache', 'rss',
            'smiley', 'acronym', 'entity',
        ];
        foreach ($always as $mode) {
            $this->assertContains($mode, $modeNames, "Always-loaded mode '$mode' missing in md syntax setting");
        }
    }

    /** In mixed modes, DW modes must still load (except those that are
     * preference-gated — see provideModeLoadingCases for the per-mode rules) */
    function testGetModesMixedModesLoadDwModes()
    {
        // DW modes that load in both dw+md and md+dw (no MD-side conflict)
        $dwAlways = [
            'emphasis', 'deleted', 'code', 'header',
            'linebreak', 'internallink', 'media', 'table',
            'monospace', 'unformatted', 'file',
        ];

        foreach (['dw+md', 'md+dw'] as $syntax) {
            $modes = (new ModeRegistry($syntax))->getModes();
            $modeNames = array_column($modes, 'mode');

            foreach ($dwAlways as $mode) {
                $this->assertContains($mode, $modeNames, "DW mode '$mode' missing in '$syntax' syntax setting");
            }
        }
    }

    /**
     * Two registries built with different syntaxes in the same request must
     * produce different mode lists — the guarantee that the registry is a
     * per-parse value, not shared global state.
     */
    function testRegistriesWithDifferentSyntaxesDiffer()
    {
        $dw = array_column((new ModeRegistry('dw'))->getModes(), 'mode');
        $md = array_column((new ModeRegistry('md'))->getModes(), 'mode');

        $this->assertContains('internallink', $dw);
        $this->assertNotContains('internallink', $md);
        $this->assertContains('gfm_emphasis', $md);
        $this->assertNotContains('gfm_emphasis', $dw);
    }

    function testAcquireSubParserReturnsParser()
    {
        $parser = $this->registry->acquireSubParser();
        $this->assertInstanceOf(Parser::class, $parser);
        $this->registry->releaseSubParser();
    }

    function testAcquireReleaseAcquireReturnsSameInstance()
    {
        // Sequential acquire/release pairs on the same key reuse the
        // pool slot — the second acquire gets the same instance because
        // it is no longer in use.
        $first = $this->registry->acquireSubParser();
        $this->registry->releaseSubParser();
        $second = $this->registry->acquireSubParser();
        $this->registry->releaseSubParser();
        $this->assertSame($first, $second);
    }

    function testNestedAcquireReturnsDifferentInstance()
    {
        // While one parser is checked out for a given exclusion key, a
        // second acquire on the same key must hand back a different
        // instance — the pool grows on demand to support re-entrancy.
        $outer = $this->registry->acquireSubParser();
        $inner = $this->registry->acquireSubParser();
        try {
            $this->assertNotSame($outer, $inner);
        } finally {
            $this->registry->releaseSubParser();
            $this->registry->releaseSubParser();
        }
    }

    function testWithSubParserReleasesEvenOnException()
    {
        try {
            $this->registry->withSubParser([], [], static function () {
                throw new \RuntimeException('boom');
            });
        } catch (\RuntimeException) {
            // expected
        }
        // After the throw, a fresh acquire on the same key must reuse
        // the pool slot — proving the release ran in the finally clause.
        $first = $this->registry->acquireSubParser([], []);
        $this->registry->releaseSubParser([], []);
        $second = $this->registry->acquireSubParser([], []);
        $this->registry->releaseSubParser([], []);
        $this->assertSame($first, $second);
    }

    function testAcquireSubParserExcludesBaseonlyByDefault()
    {
        $registry = new ModeRegistry('md');

        $parser = $registry->acquireSubParser();
        try {
            $parser->parse("# A header\n");
            // gfm_header would emit `header` and `section_open`; both absent here
            $names = array_column($parser->getHandler()->calls, 0);
            $this->assertNotContains('header', $names);
            $this->assertNotContains('section_open', $names);
        } finally {
            $registry->releaseSubParser();
        }
    }

    function testAcquireSubParserHonoursCustomExclusions()
    {
        $registry = new ModeRegistry('md');

        // With FORMATTING also excluded, gfm_emphasis is gone and `*foo*` stays literal
        $excludes = [
            ModeRegistry::CATEGORY_BASEONLY,
            ModeRegistry::CATEGORY_FORMATTING,
        ];
        $parser = $registry->acquireSubParser($excludes);
        try {
            $parser->parse("*foo*\n");
            $names = array_column($parser->getHandler()->calls, 0);
            $this->assertNotContains('emphasis_open', $names);
        } finally {
            $registry->releaseSubParser($excludes);
        }
    }

    function testSubParserPoolIsPerRegistry()
    {
        $first = $this->registry->acquireSubParser();
        $this->registry->releaseSubParser();
        $other = new ModeRegistry('dw');
        $second = $other->acquireSubParser();
        $other->releaseSubParser();
        $this->assertNotSame($first, $second);
    }

    function testAcquireSubParserDoesNotClobberMainParserModes()
    {
        // Wire the main parser up the way real callers do: addMode() attaches
        // the main parser's lexer to each mode. The sub-parser must then clone
        // these modes so its own addMode() does not overwrite those references
        // and break the main parse.
        $main = $this->registry->getModes();
        $mainParser = new Parser(new Handler($this->registry), $this->registry);
        foreach ($main as $m) {
            $mainParser->addMode($m['mode'], $m['obj']);
        }

        $mainLexers = [];
        foreach ($main as $m) {
            $this->assertNotNull(
                $m['obj']->getLexer(),
                "precondition: main mode '{$m['mode']}' must have a Lexer attached"
            );
            $mainLexers[$m['mode']] = $m['obj']->getLexer();
        }

        $this->registry->acquireSubParser();
        $this->registry->releaseSubParser();

        foreach ($main as $m) {
            $this->assertSame(
                $mainLexers[$m['mode']],
                $m['obj']->getLexer(),
                "sub-parser must not clobber main mode '{$m['mode']}'->Lexer"
            );
        }
    }

    /**
     * Verifies that each mode is loaded in the expected combinations of
     * `$conf['syntax']`. One data set per (mode, syntax) pair.
     *
     * Add new mode-gating rules to {@see provideModeLoadingCases} — each
     * entry lists the four syntax settings and whether the mode should be
     * loaded there.
     *
     * @dataProvider provideModeLoadingCases
     */
    function testModeLoadingBySyntax(string $mode, string $syntax, bool $shouldLoad): void
    {
        $modeNames = array_column((new ModeRegistry($syntax))->getModes(), 'mode');

        if ($shouldLoad) {
            $this->assertContains($mode, $modeNames, "$mode must load in '$syntax'");
        } else {
            $this->assertNotContains($mode, $modeNames, "$mode must NOT load in '$syntax'");
        }
    }

    /**
     * Data provider for {@see testModeLoadingBySyntax}.
     *
     * Declares, per parser mode, whether it should be loaded in each of the
     * four `$conf['syntax']` settings (`dw`, `md`, `dw+md`, `md+dw`).
     * Entries are expanded into one data set per (mode, syntax) pair so
     * PHPUnit reports failures with a specific label.
     *
     * Five gating categories are represented:
     *
     * - **Always**: loaded unconditionally (no syntax-specific counterpart
     *   or conflict). Covers core formatting, paragraphs, and data-driven
     *   modes (smileys, acronyms, entities).
     * - **DW-always**: loaded whenever DokuWiki is part of the syntax. Used
     *   for features that have a Markdown counterpart but no delimiter
     *   conflict (e.g. `**bold**` for emphasis).
     * - **DW-preferred**: loaded only when DokuWiki is the primary syntax.
     *   Used when the delimiter conflicts with a Markdown mode in MD-
     *   preferred settings (e.g. `__` clashes with GFM strong).
     * - **MD-always**: mirror — loaded whenever Markdown is part of the
     *   syntax. Used when the delimiter has no DokuWiki counterpart (e.g.
     *   `*` for emphasis).
     * - **MD-preferred**: mirror — loaded only when Markdown is primary.
     *   Used when the delimiter conflicts with a DokuWiki mode in DW-
     *   preferred settings (e.g. `_`, `__`, `___` clash with Underline).
     *
     * Add a new line to the `$rules` table to register additional mode-
     * gating rules.
     *
     * @return array<string, array{0: string, 1: string, 2: bool}> map from
     *     test-case label to [mode name, syntax setting, should-load]
     */
    public static function provideModeLoadingCases(): array
    {
        $rules = [
            // Always-loaded (unconditional — no syntax-specific counterpart)
            'strong'                         => ['dw' => true,  'md' => true,  'dw+md' => true,  'md+dw' => true ],
            'subscript'                      => ['dw' => true,  'md' => true,  'dw+md' => true,  'md+dw' => true ],
            'superscript'                    => ['dw' => true,  'md' => true,  'dw+md' => true,  'md+dw' => true ],
            'footnote'                       => ['dw' => true,  'md' => true,  'dw+md' => true,  'md+dw' => true ],
            'eol'                            => ['dw' => true,  'md' => true,  'dw+md' => true,  'md+dw' => true ],
            'preformatted'                   => ['dw' => true,  'md' => true,  'dw+md' => true,  'md+dw' => true ],
            'gfm_quote'                      => ['dw' => true,  'md' => true,  'dw+md' => true,  'md+dw' => true ],
            'gfm_hr'                         => ['dw' => true,  'md' => true,  'dw+md' => true,  'md+dw' => true ],
            'externallink'                   => ['dw' => true,  'md' => true,  'dw+md' => true,  'md+dw' => true ],
            'emaillink'                      => ['dw' => true,  'md' => true,  'dw+md' => true,  'md+dw' => true ],
            'windowssharelink'               => ['dw' => true,  'md' => true,  'dw+md' => true,  'md+dw' => true ],
            'notoc'                          => ['dw' => true,  'md' => true,  'dw+md' => true,  'md+dw' => true ],
            'nocache'                        => ['dw' => true,  'md' => true,  'dw+md' => true,  'md+dw' => true ],
            'rss'                            => ['dw' => true,  'md' => true,  'dw+md' => true,  'md+dw' => true ],
            'smiley'                         => ['dw' => true,  'md' => true,  'dw+md' => true,  'md+dw' => true ],
            'acronym'                        => ['dw' => true,  'md' => true,  'dw+md' => true,  'md+dw' => true ],
            'entity'                         => ['dw' => true,  'md' => true,  'dw+md' => true,  'md+dw' => true ],
            // DW-always (features with MD counterparts but no delimiter clash)
            'emphasis'                       => ['dw' => true,  'md' => false, 'dw+md' => true,  'md+dw' => true ],
            'deleted'                        => ['dw' => true,  'md' => false, 'dw+md' => true,  'md+dw' => true ],
            'code'                           => ['dw' => true,  'md' => false, 'dw+md' => true,  'md+dw' => true ],
            'header'                         => ['dw' => true,  'md' => false, 'dw+md' => true,  'md+dw' => true ],
            'linebreak'                      => ['dw' => true,  'md' => false, 'dw+md' => true,  'md+dw' => true ],
            'internallink'                   => ['dw' => true,  'md' => false, 'dw+md' => true,  'md+dw' => true ],
            'media'                          => ['dw' => true,  'md' => false, 'dw+md' => true,  'md+dw' => true ],
            'listblock'                      => ['dw' => true,  'md' => false, 'dw+md' => true,  'md+dw' => false],
            'table'                          => ['dw' => true,  'md' => false, 'dw+md' => true,  'md+dw' => true ],
            'monospace'                      => ['dw' => true,  'md' => false, 'dw+md' => true,  'md+dw' => true ],
            'unformatted'                    => ['dw' => true,  'md' => false, 'dw+md' => true,  'md+dw' => true ],
            'file'                           => ['dw' => true,  'md' => false, 'dw+md' => true,  'md+dw' => true ],
            // MD-always (`*` / `~~` have no conflicting DW counterpart)
            'gfm_emphasis'                   => ['dw' => false, 'md' => true,  'dw+md' => true,  'md+dw' => true ],
            'gfm_emphasis_strong'            => ['dw' => false, 'md' => true,  'dw+md' => true,  'md+dw' => true ],
            'gfm_deleted'                    => ['dw' => false, 'md' => true,  'dw+md' => true,  'md+dw' => true ],
            'gfm_backtick_single'            => ['dw' => false, 'md' => true,  'dw+md' => true,  'md+dw' => true ],
            'gfm_backtick_double'            => ['dw' => false, 'md' => true,  'dw+md' => true,  'md+dw' => true ],
            'gfm_header'                     => ['dw' => false, 'md' => true,  'dw+md' => true,  'md+dw' => true ],
            'gfm_link'                       => ['dw' => false, 'md' => true,  'dw+md' => true,  'md+dw' => true ],
            'gfm_media'                      => ['dw' => false, 'md' => true,  'dw+md' => true,  'md+dw' => true ],
            'gfm_code'                       => ['dw' => false, 'md' => true,  'dw+md' => true,  'md+dw' => true ],
            'gfm_file'                       => ['dw' => false, 'md' => true,  'dw+md' => true,  'md+dw' => true ],
            'gfm_table'                      => ['dw' => false, 'md' => true,  'dw+md' => true,  'md+dw' => true ],
            'gfm_escape'                     => ['dw' => false, 'md' => true,  'dw+md' => true,  'md+dw' => true ],
            'gfm_linebreak'                  => ['dw' => false, 'md' => true,  'dw+md' => true,  'md+dw' => true ],
            // MD-preferred (`_`, `__`, `___` clash with Underline in DW)
            'gfm_emphasis_underscore'        => ['dw' => false, 'md' => true,  'dw+md' => false, 'md+dw' => true ],
            'gfm_strong_underscore'          => ['dw' => false, 'md' => true,  'dw+md' => false, 'md+dw' => true ],
            'gfm_emphasis_strong_underscore' => ['dw' => false, 'md' => true,  'dw+md' => false, 'md+dw' => true ],
            'gfm_listblock'                  => ['dw' => false, 'md' => true,  'dw+md' => false, 'md+dw' => true ],
            // DW-preferred (Underline's `__` clashes with GFM strong)
            'underline'                      => ['dw' => true,  'md' => false, 'dw+md' => true,  'md+dw' => false],
        ];

        $cases = [];
        foreach ($rules as $mode => $bySyntax) {
            foreach ($bySyntax as $syntax => $shouldLoad) {
                $cases["$mode in $syntax"] = [$mode, $syntax, $shouldLoad];
            }
        }
        return $cases;
    }
}
