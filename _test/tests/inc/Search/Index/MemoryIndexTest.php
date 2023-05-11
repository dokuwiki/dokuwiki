<?php

namespace dokuwiki\test\Search\Index;

use dokuwiki\Search\Index\MemoryIndex;

class MemoryIndexTest extends AbstractIndexTest
{
    protected function getIndex()
    {
        static $count = 0;
        return new MemoryIndex('index', $count++, true);
    }

    public function testChangeRow()
    {
        $index = $this->getIndex();

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
        $index = $this->getIndex();
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
        $index = $this->getIndex();
        $this->assertFileNotExists($index->getFilename());
        $this->assertFalse($index->isDirty());

        $index->changeRow(0, '');
        $this->assertTrue($index->isDirty());
        $index->save();
        $this->assertFalse($index->isDirty());
        $this->assertEquals(1, filesize($index->getFilename())); // new line

        $index->changeRow(3, 'test');
        $this->assertTrue($index->isDirty());
        $index->save();
        $this->assertFalse($index->isDirty());
        $this->assertEquals(8, filesize($index->getFilename())); // 4 new lines + test

        $index->getRowID('test'); // existing entry
        $index->save();
        $this->assertFalse($index->isDirty());
    }

}
