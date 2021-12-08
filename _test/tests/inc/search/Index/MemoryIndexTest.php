<?php

namespace tests\Search\Index;

use dokuwiki\Search\Index\MemoryIndex;

class MemoryIndexTest extends \DokuWikiTest
{

    public function testChangeRow()
    {
        $index = new MemoryIndex(__FUNCTION__);

        $index->changeRow(5, 'test');
        $full = $this->getInaccessibleProperty($index, 'data');
        $this->assertEquals(6, count($full));

        $index->changeRow(3, 'foo');
        $full = $this->getInaccessibleProperty($index, 'data');
        $this->assertEquals(6, count($full));

        $index->changeRow(5, 'bar');
        $index->changeRow(7, 'bang');

        $full = $this->getInaccessibleProperty($index, 'data');
        $this->assertEquals(['', '', '', 'foo', '', 'bar', '', 'bang'], $full);
    }

    public function testRetrieveRow()
    {
        $index = new MemoryIndex(__FUNCTION__);
        $index->changeRow(5, 'test');
        $this->assertEquals('test', $index->retrieveRow(5));

        // out of bounds line should be empty
        $this->assertEquals('', $index->retrieveRow(100));
    }

    public function testSave()
    {
        $index = new MemoryIndex(__FUNCTION__);
        $this->assertFileNotExists($index->getFilename());
        $index->save();
        $this->assertFileExists($index->getFilename());
    }

    public function testGetRowID()
    {
        $index = new MemoryIndex(__FUNCTION__);
        $result = $index->getRowID('foo');
        $this->assertEquals(0, $result);

        $result = $index->getRowID('bar');
        $this->assertEquals(1, $result);

        $result = $index->getRowID('foo');
        $this->assertEquals(0, $result);
    }

    public function testGetRowIDs()
    {
        $index = new MemoryIndex(__FUNCTION__);
        $result = $index->getRowIDs(['foo', 'bar', 'baz']);
        $this->assertEquals(['foo' => 0, 'bar' => 1, 'baz' => 2], $result);

        $result = $index->getRowIDs(['foo', 'bang', 'baz']);
        $this->assertEquals(['foo' => 0, 'baz' => 2, 'bang' => 3], $result);
    }

}
