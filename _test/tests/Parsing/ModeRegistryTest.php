<?php

namespace dokuwiki\test\Parsing;

use dokuwiki\Parsing\ModeRegistry;
use dokuwiki\Parsing\ParserMode\ModeInterface;

class ModeRegistryTest extends \DokuWikiTest
{
    /** @var ModeRegistry */
    private $registry;

    function setUp(): void
    {
        parent::setUp();
        ModeRegistry::reset();
        $this->registry = ModeRegistry::getInstance();
    }

    function tearDown(): void
    {
        ModeRegistry::reset();
        parent::tearDown();
    }

    function testSingleton()
    {
        $this->assertSame(
            ModeRegistry::getInstance(),
            ModeRegistry::getInstance()
        );
    }

    function testResetCreatesFreshInstance()
    {
        $first = ModeRegistry::getInstance();
        ModeRegistry::reset();
        $second = ModeRegistry::getInstance();
        $this->assertNotSame($first, $second);
    }

    function testConstructorPopulatesGlobal()
    {
        global $PARSER_MODES;
        $this->assertIsArray($PARSER_MODES);
        $this->assertArrayHasKey('container', $PARSER_MODES);
        $this->assertArrayHasKey('formatting', $PARSER_MODES);
        $this->assertArrayHasKey('substition', $PARSER_MODES);
        $this->assertArrayHasKey('protected', $PARSER_MODES);
        $this->assertArrayHasKey('disabled', $PARSER_MODES);
        $this->assertArrayHasKey('paragraphs', $PARSER_MODES);
        $this->assertArrayHasKey('baseonly', $PARSER_MODES);
    }

    function testGetCategories()
    {
        global $PARSER_MODES;
        $this->assertSame($PARSER_MODES, $this->registry->getCategories());
    }

    function testGetModesForSingleCategory()
    {
        $modes = $this->registry->getModesForCategories([ModeRegistry::CATEGORY_CONTAINER]);
        $this->assertContains('listblock', $modes);
        $this->assertContains('table', $modes);
        $this->assertContains('quote', $modes);
        $this->assertContains('hr', $modes);
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
        global $PARSER_MODES;
        $this->registry->registerMode(ModeRegistry::CATEGORY_CONTAINER, 'testmode');
        $this->assertContains('testmode', $PARSER_MODES[ModeRegistry::CATEGORY_CONTAINER]);
        $this->assertContains(
            'testmode',
            $this->registry->getModesForCategories([ModeRegistry::CATEGORY_CONTAINER])
        );
    }

    function testGlobalModificationsAreVisible()
    {
        global $PARSER_MODES;
        $PARSER_MODES[ModeRegistry::CATEGORY_FORMATTING][] = 'custom_format';
        $modes = $this->registry->getModesForCategories([ModeRegistry::CATEGORY_FORMATTING]);
        $this->assertContains('custom_format', $modes);
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
            $this->assertInstanceOf(ModeInterface::class, $entry['obj']);
        }
    }

    function testGetModesContainsBuiltinModes()
    {
        $modes = $this->registry->getModes();
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

    function testLineStartMarkersEmptyByDefault()
    {
        $this->assertSame([], $this->registry->getLineStartMarkers());
    }

    function testRegisterLineStartMarkers()
    {
        $this->registry->registerLineStartMarkers('listblock', ['\\*', '\\-']);
        $markers = $this->registry->getLineStartMarkers();
        $this->assertContains('\\*', $markers);
        $this->assertContains('\\-', $markers);
    }

    function testLineStartMarkersDeduplicates()
    {
        $this->registry->registerLineStartMarkers('mode_a', ['\\*', '\\-']);
        $this->registry->registerLineStartMarkers('mode_b', ['\\-', '\\+']);
        $markers = $this->registry->getLineStartMarkers();
        $this->assertCount(3, $markers);
        $this->assertContains('\\*', $markers);
        $this->assertContains('\\-', $markers);
        $this->assertContains('\\+', $markers);
    }

    function testBlockEolModesResetWithInstance()
    {
        $this->registry->registerBlockEolMode('listblock');
        ModeRegistry::reset();
        $fresh = ModeRegistry::getInstance();
        $this->assertSame([], $fresh->getBlockEolModes());
    }

    /**
     * The default syntax setting must produce the exact same mode set as before
     * the syntax setting was introduced (no-op guarantee).
     */
    function testGetModesDefaultSyntaxMatchesLegacy()
    {
        global $conf;
        $conf['syntax'] = 'dokuwiki';
        ModeRegistry::reset();
        $registry = ModeRegistry::getInstance();
        $modes = $registry->getModes();
        $modeNames = array_column($modes, 'mode');

        // All original built-in modes must be present
        $expected = [
            'listblock', 'preformatted', 'notoc', 'nocache',
            'header', 'table', 'linebreak', 'footnote',
            'hr', 'unformatted', 'code', 'file', 'quote',
            'internallink', 'rss', 'media', 'externallink',
            'emaillink', 'windowssharelink', 'eol',
            'strong', 'emphasis', 'underline', 'monospace',
            'subscript', 'superscript', 'deleted',
            'smiley', 'acronym', 'entity',
        ];
        foreach ($expected as $mode) {
            $this->assertContains($mode, $modeNames, "Mode '$mode' missing in dokuwiki syntax setting");
        }
    }

    /** DW-only modes must be absent when syntax is 'markdown' */
    function testGetModesDwModesSkippedInMarkdownOnly()
    {
        global $conf;
        $conf['syntax'] = 'markdown';
        ModeRegistry::reset();
        $registry = ModeRegistry::getInstance();
        $modes = $registry->getModes();
        $modeNames = array_column($modes, 'mode');

        $dwOnly = [
            'emphasis', 'deleted', 'code', 'header', 'hr',
            'linebreak', 'internallink', 'media', 'listblock', 'table',
        ];
        foreach ($dwOnly as $mode) {
            $this->assertNotContains($mode, $modeNames, "DW mode '$mode' should not load in markdown-only mode");
        }
    }

    /** Always-loaded modes must still be present in markdown-only mode */
    function testGetModesAlwaysModesPresentInMarkdownOnly()
    {
        global $conf;
        $conf['syntax'] = 'markdown';
        ModeRegistry::reset();
        $registry = ModeRegistry::getInstance();
        $modes = $registry->getModes();
        $modeNames = array_column($modes, 'mode');

        $always = [
            'strong', 'monospace', 'subscript', 'superscript',
            'footnote', 'eol', 'unformatted', 'preformatted', 'file',
            'quote', 'externallink', 'emaillink', 'windowssharelink',
            'notoc', 'nocache', 'rss',
            'smiley', 'acronym', 'entity',
        ];
        foreach ($always as $mode) {
            $this->assertContains($mode, $modeNames, "Always-loaded mode '$mode' missing in markdown syntax setting");
        }
    }

    /** In mixed modes, DW modes must still load */
    function testGetModesMixedModesLoadDwModes()
    {
        $dwOnly = [
            'emphasis', 'deleted', 'code', 'header', 'hr',
            'linebreak', 'internallink', 'media', 'listblock', 'table',
        ];

        foreach (['dw+md', 'md+dw'] as $syntax) {
            global $conf;
            $conf['syntax'] = $syntax;
            ModeRegistry::reset();
            $registry = ModeRegistry::getInstance();
            $modes = $registry->getModes();
            $modeNames = array_column($modes, 'mode');

            foreach ($dwOnly as $mode) {
                $this->assertContains($mode, $modeNames, "DW mode '$mode' missing in '$syntax' syntax setting");
            }
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
        global $conf;
        $conf['syntax'] = $syntax;
        ModeRegistry::reset();
        $modeNames = array_column(ModeRegistry::getInstance()->getModes(), 'mode');

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
     * four `$conf['syntax']` settings (`dokuwiki`, `markdown`, `dw+md`,
     * `md+dw`). Entries are expanded into one data set per (mode, syntax)
     * pair so PHPUnit reports failures with a specific label.
     *
     * Three gating categories are represented:
     *
     * - **MD-always**: loaded whenever Markdown is part of the syntax. Used
     *   when the delimiter has no DokuWiki counterpart (e.g. `*` for
     *   emphasis).
     * - **MD-preferred**: loaded only when Markdown is the primary syntax.
     *   Used when the delimiter conflicts with a DokuWiki mode in DW-
     *   preferred settings (e.g. `_`, `__`, `___` clash with Underline).
     * - **DW-preferred**: mirror — loaded only when DokuWiki is primary.
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
            // MD-always (`*` / `~~` have no conflicting DW counterpart)
            'gfm_emphasis'                   => ['dokuwiki' => false, 'markdown' => true,  'dw+md' => true,  'md+dw' => true ],
            'gfm_emphasis_strong'            => ['dokuwiki' => false, 'markdown' => true,  'dw+md' => true,  'md+dw' => true ],
            'gfm_deleted'                    => ['dokuwiki' => false, 'markdown' => true,  'dw+md' => true,  'md+dw' => true ],
            'gfm_backtick_single'            => ['dokuwiki' => false, 'markdown' => true,  'dw+md' => true,  'md+dw' => true ],
            'gfm_backtick_double'            => ['dokuwiki' => false, 'markdown' => true,  'dw+md' => true,  'md+dw' => true ],
            'gfm_header'                     => ['dokuwiki' => false, 'markdown' => true,  'dw+md' => true,  'md+dw' => true ],
            'gfm_link'                       => ['dokuwiki' => false, 'markdown' => true,  'dw+md' => true,  'md+dw' => true ],
            'gfm_media'                      => ['dokuwiki' => false, 'markdown' => true,  'dw+md' => true,  'md+dw' => true ],
            'gfm_code'                       => ['dokuwiki' => false, 'markdown' => true,  'dw+md' => true,  'md+dw' => true ],
            'gfm_file'                       => ['dokuwiki' => false, 'markdown' => true,  'dw+md' => true,  'md+dw' => true ],
            // MD-preferred (`_`, `__`, `___` clash with Underline in DW)
            'gfm_emphasis_underscore'        => ['dokuwiki' => false, 'markdown' => true,  'dw+md' => false, 'md+dw' => true ],
            'gfm_strong_underscore'          => ['dokuwiki' => false, 'markdown' => true,  'dw+md' => false, 'md+dw' => true ],
            'gfm_emphasis_strong_underscore' => ['dokuwiki' => false, 'markdown' => true,  'dw+md' => false, 'md+dw' => true ],
            // DW-preferred (Underline's `__` clashes with GFM strong)
            'underline'                      => ['dokuwiki' => true,  'markdown' => false, 'dw+md' => true,  'md+dw' => false],
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
