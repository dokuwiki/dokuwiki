<?php

namespace plugin\struct\test;

// we don't have the auto loader here
spl_autoload_register(array('action_plugin_struct_autoloader', 'autoloader'));

use plugin\struct\meta\SchemaBuilder;
use plugin\struct\meta\Schema;
use plugin\struct\meta;
use plugin\struct\meta\Search;

/**
 * Tests to the DB for the struct plugin
 *
 * @group plugin_struct
 * @group plugins
 *
 */
class schemaDataDBMulti_struct_test extends \DokuWikiTest {

    protected $pluginsEnabled = array('struct', 'sqlite',);

    /** @var \helper_plugin_sqlite $sqlite */
    protected $sqlite;

    public function setUp() {
        parent::setUp();

        /** @var \helper_plugin_struct_db $sqlite */
        $sqlite = plugin_load('helper', 'struct_db');
        $this->sqlite = $sqlite->getDB();

        $testdata = array();
        $testdata['new']['new1']['sort'] = 70;
        $testdata['new']['new1']['label'] = 'testMulitColumn2';
        $testdata['new']['new1']['ismulti'] = 1;
        $testdata['new']['new1']['config'] = '{"prefix": "", "postfix": ""}';
        $testdata['new']['new1']['class'] = 'Text';
        $testdata['new']['new1']['isenabled'] = '1';
        $testdata['new']['new2']['sort'] = 40;
        $testdata['new']['new2']['label'] = 'testMulitColumn';
        $testdata['new']['new2']['ismulti'] = 1;
        $testdata['new']['new2']['config'] = '{"prefix": "", "postfix": ""}';
        $testdata['new']['new2']['class'] = 'Text';
        $testdata['new']['new2']['isenabled'] = '1';

        $testname = 'testTable';
        $testname = Schema::cleanTableName($testname);

        $builder = new SchemaBuilder($testname, $testdata);
        $builder->build();

        // revision 1
        /** @noinspection SqlResolve */
        $this->sqlite->query("INSERT INTO data_testtable (pid, rev) VALUES (?,?)", array('testpage', 123));
        /** @noinspection SqlResolve */
        $this->sqlite->query("INSERT INTO multi_testtable (colref, pid, rev, row, value) VALUES (?,?,?,?,?)",
                       array(1,'testpage',123,1,'value1.1',));
        /** @noinspection SqlResolve */
        $this->sqlite->query("INSERT INTO multi_testtable (colref, pid, rev, row, value) VALUES (?,?,?,?,?)",
                       array(1,'testpage',123,2,'value1.2',));
        /** @noinspection SqlResolve */
        $this->sqlite->query("INSERT INTO multi_testtable (colref, pid, rev, row, value) VALUES (?,?,?,?,?)",
                       array(2,'testpage',123,1,'value2.1',));
        /** @noinspection SqlResolve */
        $this->sqlite->query("INSERT INTO multi_testtable (colref, pid, rev, row, value) VALUES (?,?,?,?,?)",
                       array(2,'testpage',123,2,'value2.2',));


        // revision 2
        ///** @noinspection SqlResolve */
        $this->sqlite->query("INSERT INTO data_testtable (pid, rev) VALUES (?,?)", array('testpage', 789));
        /** @noinspection SqlResolve */
        $this->sqlite->query("INSERT INTO multi_testtable (colref, pid, rev, row, value) VALUES (?,?,?,?,?)",
                       array(1,'testpage',789,1,'value1.1a',));
        /** @noinspection SqlResolve */
        $this->sqlite->query("INSERT INTO multi_testtable (colref, pid, rev, row, value) VALUES (?,?,?,?,?)",
                       array(1,'testpage',789,2,'value1.2a',));
        /** @noinspection SqlResolve */
        $this->sqlite->query("INSERT INTO multi_testtable (colref, pid, rev, row, value) VALUES (?,?,?,?,?)",
                       array(2,'testpage',789,1,'value2.1a',));
        /** @noinspection SqlResolve */
        $this->sqlite->query("INSERT INTO multi_testtable (colref, pid, rev, row, value) VALUES (?,?,?,?,?)",
                       array(2,'testpage',789,2,'value2.2a',));


        // revision 1 of different page
        /** @noinspection SqlResolve */
        $this->sqlite->query("INSERT INTO data_testtable (pid, rev) VALUES (?,?)", array('testpage2', 789));
        /** @noinspection SqlResolve */
        $this->sqlite->query("INSERT INTO multi_testtable (colref, pid, rev, row, value) VALUES (?,?,?,?,?)",
                             array(1,'testpage2',789,1,'value1.1a',));
        /** @noinspection SqlResolve */
        $this->sqlite->query("INSERT INTO multi_testtable (colref, pid, rev, row, value) VALUES (?,?,?,?,?)",
                             array(2,'testpage2',789,1,'value2.1a',));
    }

    public function tearDown() {
        parent::tearDown();

        /** @var \helper_plugin_struct_db $sqlite */
        $sqlite = plugin_load('helper', 'struct_db');
        $sqlite->resetDB();
    }

    public function test_getDataFromDB_currentRev() {

        // act
        $schemaData = new mock\SchemaData('testtable','testpage', "");
        $schemaData->setCorrectTimestamp('testpage');
        $actual_data = $schemaData->getDataFromDB();
//        print_r($schemaData->buildGetDataSQL(array(), array(1,2)));

        $expected_data = array(
            array(
                'col1' => 'value1.1a'. Search::CONCAT_SEPARATOR . 'value1.2a',
                'col2' => 'value2.1a'. Search::CONCAT_SEPARATOR . 'value2.2a',
            ),
        );


        $this->assertEquals($expected_data, $actual_data , '');
    }
}
