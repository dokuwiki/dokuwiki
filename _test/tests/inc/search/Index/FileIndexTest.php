<?php

use dokuwiki\Search\Index\AbstractIndex;
use dokuwiki\Search\Index\FileIndex;

class FileIndexTest extends \DokuWikiTest
{
    /**
     * @return AbstractIndex
     */
    protected function getIndex() {
        static $count = 0;
        return new FileIndex('index', $count++);
    }

    public function testChangeRow()
    {

        $index = $this->getIndex();

        $index->changeRow(5, 'test');
        $full = file($index->getFilename(), FILE_IGNORE_NEW_LINES);
        $this->assertEquals(6, count($full));

        $index->changeRow(3, 'foo');
        $full = file($index->getFilename(), FILE_IGNORE_NEW_LINES);
        $this->assertEquals(6, count($full));

        $index->changeRow(5, 'bar');
        $index->changeRow(7, 'bang');

        $full = file($index->getFilename(), FILE_IGNORE_NEW_LINES);
        $this->assertEquals(['', '', '', 'foo', '', 'bar', '', 'bang'], $full);
    }

    public function testRetrieveRow()
    {
        $index = $this->getIndex();
        $index->changeRow(5, 'test');
        $this->assertEquals('test', $index->retrieveRow(5));

        // out of bounds line should be empty, but pad the file
        $this->assertEquals('', $index->retrieveRow(10));
        $full = file($index->getFilename(), FILE_IGNORE_NEW_LINES);
        $this->assertEquals(11, count($full));
    }

    public function testGetRowId()
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
}
