<?php

namespace dokuwiki\test\Search\Index;

use dokuwiki\Search\Index\AbstractIndex;

abstract class AbstractIndexTest extends \DokuWikiTest
{

    /**
     * @return AbstractIndex
     */
    abstract protected function getIndex();

    public function testGetRowID()
    {
        $index = $this->getIndex();
        $result = $index->getRowID('foo');
        $this->assertEquals(0, $result);

        $result = $index->getRowID('bar');
        $this->assertEquals(1, $result);

        $result = $index->getRowID('foo');
        $this->assertEquals(0, $result);
    }

    public function testGetRowIDs()
    {
        $index = $this->getIndex();
        $result = $index->getRowIDs(['foo', 'bar', 'baz']);
        $this->assertEquals(['foo' => 0, 'bar' => 1, 'baz' => 2], $result);

        $result = $index->getRowIDs(['foo', 'bang', 'baz']);
        $this->assertEquals(['foo' => 0, 'baz' => 2, 'bang' => 3], $result);
    }

    public function testRetrieve()
    {
        $index = $this->getIndex();
        $index->getRowIDs(['foo', 'bar', 'baz']); // add data

        $this->assertEquals('bar', $index->retrieveRow(1));
        $this->assertEquals('', $index->retrieveRow(5)); // non existent, but will be created with padding

        // rows up to 5 exist now, 7 does not and is ignored
        $this->assertEquals([0 => 'foo', 2 => 'baz', 4 => ''], $index->retrieveRows([0, 2, 4, 7]));
    }

    public function testSearch()
    {
        $index = $this->getIndex();
        $index->getRowIDs(['foo', 'bar', 'baz', 'bazzel']);

        $result = $index->search('/^ba.$/');
        $this->assertEquals(
            [1 => 'bar', 2 => 'baz'],
            $result
        );
    }
}
