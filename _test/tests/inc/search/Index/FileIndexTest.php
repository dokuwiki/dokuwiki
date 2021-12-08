<?php

namespace tests\Search\Index;

use dokuwiki\Search\Index\FileIndex;

class FileIndexTest extends \DokuWikiTest
{

    public function testChangeRow()
    {

        $index = new FileIndex(__FUNCTION__);

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
        $index = new FileIndex(__FUNCTION__);
        $index->changeRow(5, 'test');
        $this->assertEquals('test', $index->retrieveRow(5));

        // out of bounds line should be empty
        $this->assertEquals('', $index->retrieveRow(100));
    }

    public function testAccessValue()
    {
        $index = new FileIndex(__FUNCTION__);
        $result = $index->accessValue('foo');
        $this->assertEquals(0, $result);

        $result = $index->accessValue('bar');
        $this->assertEquals(1, $result);

        $result = $index->accessValue('foo');
        $this->assertEquals(0, $result);
    }

    public function testAccessValues()
    {
        $index = new FileIndex(__FUNCTION__);
        $result = $index->accessValues(['foo', 'bar', 'baz']);
        $this->assertEquals(['foo' => 0, 'bar' => 1, 'baz' => 2], $result);

        $result = $index->accessValues(['foo', 'bang', 'baz']);
        $this->assertEquals(['foo' => 0, 'baz' => 2, 'bang' => 3], $result);

    }
}
