<?php

namespace tests\Search\Index;

use dokuwiki\Search\Index\TupleIndex;

class TupleIndexTest extends \DokuWikiTest
{

    public function testChangeRow()
    {
        $index = new TupleIndex(__FUNCTION__);

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
        $index = new TupleIndex(__FUNCTION__);
        $index->changeRow(5, 'test');
        $this->assertEquals('test', $index->retrieveRow(5));

        // out of bounds line should be empty
        $this->assertEquals('', $index->retrieveRow(100));
    }

    public function testSave()
    {
        $index = new TupleIndex(__FUNCTION__);
        $this->assertFileNotExists($index->getFilename());
        $index->save();
        $this->assertFileExists($index->getFilename());
    }

    /**
     * @see testUpdateTuple
     */
    public function provideUpdateTuple()
    {
        return [
            ['', 'foo', 3, 'foo*3'],
            ['', 17, 3, '17*3'],
            ['foo*2', 'foo', 3, 'foo*3'],
            ['17*2', 17, 3, '17*3'],
            ['bar*2:foo*2', 'foo', 3, 'foo*3:bar*2'],
            ['18*2:17*2', 17, 3, '17*3:18*2'],
        ];
    }

    /**
     * @dataProvider provideUpdateTuple
     */
    public function testUpdateTuple($record, $key, $count, $expect)
    {
        $index = new TupleIndex(__FUNCTION__);
        $result = $this->callInaccessibleMethod($index, 'updateTuple', [$record, $key, $count]);
        $this->assertEquals($result, $expect);
    }

    public function testAggregateTupleCounts()
    {
        $index = new TupleIndex(__FUNCTION__);
        $record = '5*10:foo*2:14*100::bar*7';
        $result = $this->callInaccessibleMethod($index, 'aggregateTupleCounts', [$record]);
        $this->assertEquals(119, $result);
    }

    public function testParseTuples()
    {
        $index = new TupleIndex(__FUNCTION__);
        $record = '5*10:foo*2:14*100::bar*7';
        $keys = [5 => 'first', 'bar' => 'second', 'foo' => null];
        $expect = ['first' => 10, 'second' => 7];
        $result = $this->callInaccessibleMethod($index, 'parseTuples', [$record, $keys]);
        $this->assertEquals($expect, $result);
    }
}
