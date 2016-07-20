<?php

namespace dokuwiki\plugin\struct\test;
use dokuwiki\plugin\struct\test\mock\QueryBuilder;

/**
 * @group plugin_struct
 * @group plugins
 */
class QueryBuilder_struct_test extends StructTest {

    public function test_join() {
        $qb = new QueryBuilder();

        $qb->addTable('first');
        $qb->addTable('second');
        $qb->addTable('third');

        $qb->addLeftJoin('second', 'fourth', 'fourth' , 'second.foo=fourth.foo');
        $this->assertEquals(array('first','second','fourth','third'), array_keys($qb->from));
    }

    public function test_placeholders() {
        $qb = new QueryBuilder();


        $foo = $qb->addValue('foo');
        $bar = $qb->addValue('bar');

        $input = "this is $foo and $bar and $foo again";
        $expect = "this is ? and ? and ? again";
        $values = array('foo', 'bar', 'foo');

        $output = $qb->fixPlaceholders($input);

        $this->assertEquals($expect, $output[0]);
        $this->assertEquals($values, $output[1]);
    }

    /**
     * @expectedException \dokuwiki\plugin\struct\meta\StructException
     */
    public function test_placeholderfail() {
        $qb = new QueryBuilder();
        $qb->fixPlaceholders('this has unknown placeholder :!!val7!!:');
    }
}
