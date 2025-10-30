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
            // Test tuples without explicit count (implicit count of 1)
            ['foo', 'foo', 3, 'foo*3'],
            ['17', 17, 3, '17*3'],
            ['bar:foo', 'foo', 3, 'foo*3:bar'],
            ['bar*2:foo', 'foo', 5, 'foo*5:bar*2'],
            ['18:17', 17, 3, '17*3:18'],
            ['18:17', 19, 1, '19:18:17'],
            // existing 1 counts are not updated unless touched directly
            ['foo*4:bar*1:baz*3', 'uff', 2, 'uff*2:foo*4:bar*1:baz*3'],
            ['foo*4:bar*1:baz*3', 'bar', 1, 'bar:foo*4:baz*3'],
            // 0 is a valid entity
            ['', 0, 1, '0'],
            ['0*7', 0, 6, '0*6'],
            ['foo:bar*3', 0, 1, '0:foo:bar*3'],
            // frequency of 0 deletes
            ['', 7, 0, ''],
            ['0', 0, 0, ''],
            ['foo*6:bar*3:baz', 'bar', 0, 'foo*6:baz'],
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

    /**
     * @see testAggregateTupleCounts
     */
    public function provideAggregateTupleCounts()
    {
        return [
            ['5*10:foo*2:14*100::bar*7', 119],
            // Test with tuples without explicit count (implicit count of 1)
            ['5:foo:14::bar', 4],
            ['5*10:foo:14*100', 111],
            ['key1:key2:key3', 3],
            ['', 0],
            ['single', 1],
        ];
    }

    /**
     * @dataProvider provideAggregateTupleCounts
     */
    public function testAggregateTupleCounts($record, $expected)
    {
        $result = TupleOps::aggregateTupleCounts($record);
        $this->assertEquals($expected, $result);
    }

    /**
     * @see testParseTuples
     */
    public function provideParseTuples()
    {
        return [
            // Original test case
            [
                '5*10:foo*2:14*100::bar*7',
                [5 => 'first', 'bar' => 'second', 'foo' => null],
                ['first' => 10, 'second' => 7]
            ],
            // Test with tuples without explicit count (implicit count of 1)
            [
                '5:foo:14::bar',
                [5 => 'first', 'bar' => 'second', 'foo' => null],
                ['first' => 1, 'second' => 1]
            ],
            // Mixed: some with count, some without
            [
                '5*10:foo:14*100',
                [5 => 'first', 'foo' => 'second', 14 => 'third'],
                ['first' => 10, 'second' => 1, 'third' => 100]
            ],
            // No filter map (returns all with original keys)
            [
                '5*10:foo*2:bar',
                null,
                [5 => 10, 'foo' => 2, 'bar' => 1]
            ],
            // Empty record
            [
                '',
                [5 => 'first'],
                []
            ],
        ];
    }

    /**
     * @dataProvider provideParseTuples
     */
    public function testParseTuples($record, $keys, $expect)
    {
        $result = TupleOps::parseTuples($record, $keys);
        $this->assertEquals($expect, $result);
    }

}
