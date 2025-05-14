<?php

namespace dokuwiki\test\Treebuilder;

use dokuwiki\TreeBuilder\PageTreeBuilder;
use DokuWikiTest;

class PageTreeBuilderTest extends DokuWikiTest
{
    protected $originalDataDir;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Create a test page hierarchy
        saveWikiText('namespace:start', 'This is the start page', 'test');
        saveWikiText('namespace:page1', 'This is page 1', 'test');
        saveWikiText('namespace:page2', 'This is page 2', 'test');
        saveWikiText('namespace:subns:start', 'This is the subns start page', 'test');
        saveWikiText('namespace:subns:page3', 'This is page 3 in subns', 'test');
        saveWikiText('namespace:subns:deeper:start', 'This is the deeper start page', 'test');
        saveWikiText('namespace:subns:deeper:page4', 'This is page 4 in deeper', 'test');
    }

    public function setUp(): void
    {
        parent::setUp();
        global $conf;
        $this->originalDataDir = $conf['datadir'];
    }

    public function tearDown(): void
    {
        global $conf;
        $conf['datadir'] = $this->originalDataDir;
        parent::tearDown();
    }

    public function treeConfigProvider()
    {
        return [
            'Default configuration' => [
                'namespace' => 'namespace',
                'depth' => -1,
                'flags' => 0,
                'expected' => [
                    '+namespace:start',
                    '+namespace:page1',
                    '+namespace:page2',
                    '+namespace:subns',
                    '++namespace:subns:start',
                    '++namespace:subns:page3',
                    '++namespace:subns:deeper',
                    '+++namespace:subns:deeper:start',
                    '+++namespace:subns:deeper:page4'
                ]
            ],
            'Depth limit 1' => [
                'namespace' => 'namespace',
                'depth' => 1,
                'flags' => 0,
                'expected' => [
                    '+namespace:start',
                    '+namespace:page1',
                    '+namespace:page2',
                    '+namespace:subns',
                    '++namespace:subns:start',
                    '++namespace:subns:page3',
                    '++namespace:subns:deeper'
                ]
            ],
            'Depth limit 1 with NS_AS_STARTPAGE' => [
                'namespace' => 'namespace',
                'depth' => 1,
                'flags' => PageTreeBuilder::FLAG_NS_AS_STARTPAGE,
                'expected' => [
                    '+namespace:page1',
                    '+namespace:page2',
                    '+namespace:subns:start',
                    '++namespace:subns:page3',
                    '++namespace:subns:deeper:start'
                ]
            ],
            'FLAG_NO_NS' => [
                'namespace' => 'namespace',
                'depth' => -1,
                'flags' => PageTreeBuilder::FLAG_NO_NS,
                'expected' => [
                    '+namespace:start',
                    '+namespace:page1',
                    '+namespace:page2'
                ]
            ],
            'FLAG_NO_PAGES' => [
                'namespace' => 'namespace',
                'depth' => -1,
                'flags' => PageTreeBuilder::FLAG_NO_PAGES,
                'expected' => [
                    '+namespace:subns',
                    '++namespace:subns:deeper'
                ]
            ],
            'FLAG_NS_AS_STARTPAGE' => [
                'namespace' => 'namespace',
                'depth' => -1,
                'flags' => PageTreeBuilder::FLAG_NS_AS_STARTPAGE,
                'expected' => [
                    '+namespace:page1',
                    '+namespace:page2',
                    '+namespace:subns:start',
                    '++namespace:subns:page3',
                    '++namespace:subns:deeper:start',
                    '+++namespace:subns:deeper:page4'
                ]
            ],
            'Combined FLAG_NO_NS and FLAG_NS_AS_STARTPAGE' => [
                'namespace' => 'namespace',
                'depth' => -1,
                'flags' => PageTreeBuilder::FLAG_NO_NS | PageTreeBuilder::FLAG_NS_AS_STARTPAGE,
                'expected' => [
                    '+namespace:page1',
                    '+namespace:page2'
                ]
            ],
            'FLAG_SELF_TOP' => [
                'namespace' => 'namespace',
                'depth' => -1,
                'flags' => PageTreeBuilder::FLAG_SELF_TOP,
                'expected' => [
                    '+namespace',
                    '++namespace:start',
                    '++namespace:page1',
                    '++namespace:page2',
                    '++namespace:subns',
                    '+++namespace:subns:start',
                    '+++namespace:subns:page3',
                    '+++namespace:subns:deeper',
                    '++++namespace:subns:deeper:start',
                    '++++namespace:subns:deeper:page4'
                ]
            ],
        ];
    }


    /**
     * @dataProvider treeConfigProvider
     */
    public function testPageTreeConfigurations(string $namespace, int $depth, int $flags, array $expected)
    {
        $tree = new PageTreeBuilder($namespace, $depth);
        if ($flags) {
            $tree->addFlag($flags);
        }
        $tree->generate();

        $result = explode("\n", (string)$tree);
        sort($expected);
        sort($result);

        $this->assertEquals($expected, $result);
    }

    /**
     * This is the same test as above, but pretending that our data directory is in our test namespace.
     *
     * @dataProvider treeConfigProvider
     */
    public function testTopLevelTree(string $namespace, int $depth, int $flags, array $expected)
    {
        global $conf;
        $conf['datadir'] .= '/namespace';

        $expected = array_map(function ($item) use ($namespace) {
            return preg_replace('/namespace:?/', '', $item);
        }, $expected);

        $namespace = '';
        $this->testPageTreeConfigurations($namespace, $depth, $flags, $expected);
    }


    public function testPageTreeLeaves()
    {
        $tree = new PageTreeBuilder('namespace');
        $tree->generate();

        $leaves = $tree->getLeaves();
        $branches = $tree->getBranches();

        // Test that we have both leaves and branches
        $this->assertGreaterThan(0, count($leaves), 'Should have leaf pages');
        $this->assertGreaterThan(0, count($branches), 'Should have branch pages');

        // The total should equal all pages
        $this->assertEquals(count($tree->getAll()), count($leaves) + count($branches),
            'Leaves + branches should equal total pages');
    }
}
