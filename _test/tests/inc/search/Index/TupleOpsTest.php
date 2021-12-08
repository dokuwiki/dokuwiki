<?php

namespace tests\Search\Index;

use dokuwiki\Search\Index\TupleOps;

class TupleOpsTest extends \DokuWikiTest
{
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
        $result = TupleOps::updateTuple($record, $key, $count);
        $this->assertEquals($result, $expect);
    }

    public function testAggregateTupleCounts()
    {
        $record = '5*10:foo*2:14*100::bar*7';
        $result = TupleOps::aggregateTupleCounts($record);
        $this->assertEquals(119, $result);
    }

    public function testParseTuples()
    {
        $record = '5*10:foo*2:14*100::bar*7';
        $keys = [5 => 'first', 'bar' => 'second', 'foo' => null];
        $expect = ['first' => 10, 'second' => 7];
        $result = TupleOps::parseTuples($record, $keys);
        $this->assertEquals($expect, $result);
    }

}
