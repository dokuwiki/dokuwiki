<?php

namespace dokuwiki\plugin\struct\test;
use dokuwiki\plugin\struct\test\mock\QueryBuilder;

/**
 * @group plugin_struct
 * @group plugins
 */
class QueryBuilderOther_struct_test extends StructTest {

    public function test_order_by() {
        $qb = new QueryBuilder();

        $qb->addTable('first', 'T1');
        $qb->addOrderBy('Q.foo');

        $expectedSQL = '
            SELECT FROM first AS T1 WHERE ORDER BY Q.foo
';

        list($actual_sql, $actual_opts) = $qb->getSQL();
        $this->assertEquals($this->cleanWS($expectedSQL), $this->cleanWS($actual_sql));
        $this->assertEquals(array(), $actual_opts);

    }

    public function test_group_by() {
        $qb = new QueryBuilder();

        $qb->addTable('first', 'T1');
        $qb->addGroupByColumn('T1', 'foo');
        $qb->addGroupByStatement('T2.bar');

        $expectedSQL = '
            SELECT FROM first AS T1 WHERE GROUP BY T1.foo, T2.bar
';

        list($actual_sql, $actual_opts) = $qb->getSQL();
        $this->assertEquals($this->cleanWS($expectedSQL), $this->cleanWS($actual_sql));
        $this->assertEquals(array(), $actual_opts);
    }

    /**
     * @expectedException \dokuwiki\plugin\struct\meta\StructException
     */
    public function test_groupby_missing_alias() {
        $qb = new QueryBuilder();

        $qb->addTable('first', 'T1');
        $qb->addGroupByColumn('T2', 'foo');
    }
}
