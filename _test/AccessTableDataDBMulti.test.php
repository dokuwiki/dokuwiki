<?php

namespace dokuwiki\plugin\struct\test;

use dokuwiki\plugin\struct\meta\SchemaBuilder;
use dokuwiki\plugin\struct\meta\Schema;
use dokuwiki\plugin\struct\meta;
use dokuwiki\plugin\struct\meta\Search;

/**
 * Tests to the DB for the struct plugin
 *
 * @group plugin_struct
 * @group plugins
 *
 */
class AccessTableDataDBMulti_struct_test extends StructTest {

    /** @var \helper_plugin_sqlite $sqlite */
    protected $sqlite;

    public function setUp() {
        parent::setUp();

        /** @var \helper_plugin_struct_db $sqlite */
        $sqlite = plugin_load('helper', 'struct_db');
        $this->sqlite = $sqlite->getDB();

        $this->loadSchemaJSON('testtable', 'testtable2', 100);

        // revision 1
        $this->saveData(
            'testpage',
            'testtable',
            array(
                'testMulitColumn2' => array('value1.1', 'value1.2'),
                'testMulitColumn' => array('value2.1', 'value2.2')
            ),
            123
        );

        // revision 2
        $this->saveData(
            'testpage',
            'testtable',
            array(
                'testMulitColumn2' => array('value1.1a', 'value1.2a'),
                'testMulitColumn' => array('value2.1a', 'value2.2a')
            ),
            789
        );

        // revision 1 of different page
        $this->saveData(
            'testpage2',
            'testtable',
            array(
                'testMulitColumn2' => array('value1.1a'),
                'testMulitColumn' => array('value2.1a')
            ),
            789
        );
    }

    public function test_getDataFromDB_currentRev() {

        // act
        $schemaData = mock\AccessTable::byTableName('testtable', 'testpage');
        $actual_data = $schemaData->getDataFromDB();

        $expected_data = array(
            array(
                'col1' => 'value1.1a' . Search::CONCAT_SEPARATOR . 'value1.2a',
                'col2' => 'value2.1a' . Search::CONCAT_SEPARATOR . 'value2.2a',
                'PID' => 'testpage',
            ),
        );

        $this->assertEquals($expected_data, $actual_data, '');
    }
}
