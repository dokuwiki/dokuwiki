<?php

namespace dokuwiki\plugin\struct\test;

use dokuwiki\plugin\struct\meta\QueryBuilderWhere;

/**
 * @group plugin_struct
 * @group plugins
 */
class QueryBuilderWhere_struct_test extends StructTest {

    public function test_sql() {
        $where = new QueryBuilderWhere();

        $where->whereAnd('foo = foo');
        $this->assertEquals(
            $this->cleanWS('(foo = foo)'),
            $this->cleanWS($where->toSQL())
        );

        $where->whereAnd('bar = bar');
        $this->assertEquals(
            $this->cleanWS('(foo = foo AND bar = bar)'),
            $this->cleanWS($where->toSQL())
        );

        $sub = $where->whereSubAnd();
        $this->assertEquals(
            $this->cleanWS('(foo = foo AND bar = bar)'),
            $this->cleanWS($where->toSQL())
        );

        $sub->whereAnd('zab = zab');
        $this->assertEquals(
            $this->cleanWS('(foo = foo AND bar = bar AND (zab = zab))'),
            $this->cleanWS($where->toSQL())
        );

        $sub->whereOr('fab = fab');
        $this->assertEquals(
            $this->cleanWS('(foo = foo AND bar = bar AND (zab = zab OR fab = fab))'),
            $this->cleanWS($where->toSQL())
        );
    }
}
