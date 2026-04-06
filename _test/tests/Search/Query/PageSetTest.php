<?php

namespace dokuwiki\test\Search\Query;

use dokuwiki\Search\Query\PageSet;

class PageSetTest extends \DokuWikiTest
{
    public function testIntersect()
    {
        $a = new PageSet(['p1' => 2, 'p2' => 3, 'p3' => 1]);
        $b = new PageSet(['p1' => 1, 'p3' => 4]);

        $result = $a->intersect($b);

        $this->assertEquals(['p1' => 3, 'p3' => 5], $result->getPages());
    }

    public function testUnite()
    {
        $a = new PageSet(['p1' => 2, 'p2' => 3]);
        $b = new PageSet(['p1' => 1, 'p3' => 4]);

        $result = $a->unite($b);

        $this->assertEquals(['p1' => 3, 'p2' => 3, 'p3' => 4], $result->getPages());
    }

    public function testSubtract()
    {
        $a = new PageSet(['p1' => 2, 'p2' => 3, 'p3' => 1]);
        $b = new PageSet(['p2' => 1]);

        $result = $a->subtract($b);

        $this->assertEquals(['p1' => 2, 'p3' => 1], $result->getPages());
    }

    public function testIsEmpty()
    {
        $this->assertTrue((new PageSet())->isEmpty());
        $this->assertTrue((new PageSet([]))->isEmpty());
        $this->assertFalse((new PageSet(['p1' => 1]))->isEmpty());
    }

    public function testIntersectNoOverlap()
    {
        $a = new PageSet(['p1' => 1]);
        $b = new PageSet(['p2' => 1]);

        $this->assertEquals([], $a->intersect($b)->getPages());
    }
}
