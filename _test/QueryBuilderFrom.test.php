<?php

namespace dokuwiki\plugin\struct\test;
use dokuwiki\plugin\struct\meta\QueryBuilder;

/**
 * @group plugin_struct
 * @group plugins
 */
class QueryBuilderFrom_struct_test extends StructTest {

    public function test_join() {
        $qb = new QueryBuilder();

        $qb->addTable('first', 'T1');
        $qb->addTable('second', 'T2');
        $qb->addTable('third', 'T3');

        $qb->addLeftJoin('T2', 'fourth', 'T4' , 'T2.foo=T4.foo');

        $expectedSQL = '
            SELECT FROM first AS T1, second AS T2 LEFT OUTER JOIN fourth AS T4
            ON T2.foo = T4.foo, third AS T3 WHERE
';

        list($actual_sql, $actual_opts) = $qb->getSQL();
        $this->assertEquals($this->cleanWS($expectedSQL), $this->cleanWS($actual_sql));
        $this->assertEquals(array(), $actual_opts);
    }

    /**
     * @expectedException \dokuwiki\plugin\struct\meta\StructException
     */
    public function test_table_alias_exception(){
        $qb = new QueryBuilder();

        $qb->addTable('first', 'T1');
        $qb->addTable('second', 'T1');
    }

    /**
     * @expectedException \dokuwiki\plugin\struct\meta\StructException
     */
    public function test_leftjoin_missing_alias_exception(){
        $qb = new QueryBuilder();

        $qb->addTable('first', 'T1');
        $qb->addLeftJoin('T2', 'third', 'T3', 'T2.bar = T3.bar');
    }

    /**
     * @expectedException \dokuwiki\plugin\struct\meta\StructException
     */
    public function test_leftjoin_existing_alias_exception(){
        $qb = new QueryBuilder();

        $qb->addTable('first', 'T1');
        $qb->addTable('second', 'T2');
        $qb->addLeftJoin('T2', 'third', 'T1', 'T2.bar = T1.bar');
    }
}
