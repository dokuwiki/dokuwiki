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

        $index->save();
        $full = file($index->getFilename(), FILE_IGNORE_NEW_LINES);
        $this->assertEquals(['', '', '', 'foo', '', 'bar', '', 'bang'], $full);
    }

    public function testRetrieveRow()
    {
        $index = new MemoryIndex(__FUNCTION__);
        $index->changeRow(5, 'test');
        $this->assertEquals('test', $index->retrieveRow(5));

        // out of bounds line should be empty, but pad the file
        $this->assertEquals('', $index->retrieveRow(10));
        $index->save();
        $full = file($index->getFilename(), FILE_IGNORE_NEW_LINES);
        $this->assertEquals(11, count($full));
    }

    public function testSave()
    {
        $index = new MemoryIndex(__FUNCTION__);
        $this->assertFileNotExists($index->getFilename());
        $index->save();
        $this->assertFileExists($index->getFilename());
        $this->assertEquals(0, filesize($index->getFilename())); // empty

        $index->changeRow(0, '');
        $index->save();
        $this->assertEquals(1, filesize($index->getFilename())); // new line

        $index->changeRow(3, 'test');
        $index->save();
        $this->assertEquals(8, filesize($index->getFilename())); // 4 new lines + test
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
