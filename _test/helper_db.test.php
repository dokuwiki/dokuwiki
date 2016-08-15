<?php

namespace dokuwiki\plugin\struct\test;

use dokuwiki\plugin\struct\meta;

/**
 * @group plugin_struct
 * @group plugins
 */
class helper_db_struct_test extends StructTest {

    public function setUp() {
        parent::setUp();

        $this->loadSchemaJSON('schema1');
        $this->loadSchemaJSON('schema2');
    }

    public function test_json() {
        /** @var \helper_plugin_struct_db $helper */
        $helper = plugin_load('helper', 'struct_db');
        $sqlite = $helper->getDB();

        $res = $sqlite->query("SELECT JSON('foo', 'bar') ");
        $result = $sqlite->res2single($res);
        $sqlite->res_close($res);
        $expect = '["foo","bar"]';
        $this->assertEquals($expect, $result);

        $res = $sqlite->query("SELECT JSON(id, tbl) AS col FROM schemas");
        $result = $sqlite->res2arr($res);
        $sqlite->res_close($res);

        $expect = array(
            array('col' => '[1,"schema1"]'),
            array('col' => '[2,"schema2"]'),
        );
        $this->assertEquals($expect, $result);
    }

}
