<?php

namespace dokuwiki\test\Search\Index;

use dokuwiki\Search\Index\FileIndex;

class FileIndexTest extends AbstractIndexTest
{
    protected function getIndex()
    {
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

}
