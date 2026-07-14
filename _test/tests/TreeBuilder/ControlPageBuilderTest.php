<?php

namespace dokuwiki\test\Treebuilder;

use dokuwiki\TreeBuilder\ControlPageBuilder;
use DokuWikiTest;

class ControlPageBuilderTest extends DokuWikiTest
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        saveWikiText('simple', file_get_contents(__DIR__ . '/cp/simple.txt'), 'test');
        saveWikiText('foo:complex', file_get_contents(__DIR__ . '/cp/complex.txt'), 'test');
    }

    public function testSimpleParsing()
    {
        $control = new ControlPageBuilder('simple');
        $control->generate();
        
        $expected = [
            '+briefs:start',
            '+qhsr:start',
            '++qhsr:q',
            '++qhsr:cert',
            '++qhsr:hse:start',
            '++qhsr:engsystems',
            '++qhsr:performance',
            '++qhsr:competence',
            '++qhsr:ashford',
            '++qhsr:training',
            '+tech:start',
            '+https://homepage.company.com'
        ];
        
        $result = explode("\n", (string)$control);
        sort($expected);
        sort($result);
        
        $this->assertEquals($expected, $result);
        
        // Additional structure tests
        $top = $control->getTop();
        $this->assertEquals(4, count($top->getChildren()));
        $this->assertEquals(1, count($top->getChildren()[0]->getParents()));
        $this->assertEquals(4, count($top->getChildren()[1]->getSiblings()));
        $this->assertEquals(8, count($top->getChildren()[1]->getChildren()));

        $this->assertEquals(12, count($control->getAll()));
        $this->assertEquals(11, count($control->getLeaves()));
        $this->assertEquals(1, count($control->getBranches()));
    }

    /**
     * Parse the complex example with different flags
     *
     * @return array[]
     * @see testComplexParsing
     */
    public function complexProvider()
    {
        return [
            'No flags' => [
                'flags' => 0,
                'expected' => [
                    '+content',
                    '+foo:this',
                    '+foo:bar',
                    '+foo:another_link',
                    '+https://www.google.com',
                    '+relativeup',
                    '+foo2:this',
                    '++foo2:deeper:item',
                    '+++foo2:deeper:evendeeper:item',
                    '+foo:blarg:down',
                    '+toplevel',
                    '+foo:link',
                ]
            ],
            'FLAG_NOEXTERNAL' => [
                'flags' => ControlPageBuilder::FLAG_NOEXTERNAL,
                'expected' => [
                    '+content',
                    '+foo:this',
                    '+foo:bar',
                    '+foo:another_link',
                    '+relativeup',
                    '+foo2:this',
                    '++foo2:deeper:item',
                    '+++foo2:deeper:evendeeper:item',
                    '+foo:blarg:down',
                    '+toplevel',
                    '+foo:link',
                ]
            ],
            'FLAG_NOINTERNAL' => [
                'flags' => ControlPageBuilder::FLAG_NOINTERNAL,
                'expected' => [
                    '+https://www.google.com',
                ]
            ],
        ];
    }

    /**
     * @dataProvider complexProvider
     * @param int $flags
     * @param array $expected
     * @return void
     */
    public function testComplexParsing(int $flags, array $expected)
    {
        $control = new ControlPageBuilder('foo:complex');
        $control->addFlag($flags);
        $control->generate();
        
        $result = explode("\n", (string)$control);
        sort($expected);
        sort($result);
        
        $this->assertEquals($expected, $result);
    }

    public function testNonExisting()
    {
        $this->expectException(\RuntimeException::class);
        $control = new ControlPageBuilder('does:not:exist');
        $control->generate();
        $foo = $control->getAll();
    }
}
