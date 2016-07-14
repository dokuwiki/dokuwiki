<?php

namespace dokuwiki\plugin\struct\test;
use dokuwiki\plugin\struct\meta\QueryBuilder;

/**
 * @group plugin_struct
 * @group plugins
 */
class QueryBuilderSelect_struct_test extends StructTest {

    public function test_simple_select() {
        $qb = new QueryBuilder();

        $qb->addTable('first', 'T1');
        $qb->addSelectColumn('T1','colbar','asAlias');


        $expectedSQL = '
            SELECT T1.colbar AS asAlias FROM first AS T1 WHERE
';

        list($actual_sql, $actual_opts) = $qb->getSQL();
        $this->assertEquals($this->cleanWS($expectedSQL), $this->cleanWS($actual_sql));
        $this->assertEquals(array(), $actual_opts);
    }

    /**
     * @expectedException \dokuwiki\plugin\struct\meta\StructException
     */
    public function test_missing_alias() {
        $qb = new QueryBuilder();

        $qb->addTable('first', 'T1');
        $qb->addSelectColumn('WrongAlias','colbar','colAlias');
    }

}
