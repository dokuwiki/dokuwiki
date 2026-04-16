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
}
