<?php

namespace plugin\struct\test;

// we don't have the auto loader here
spl_autoload_register(array('action_plugin_struct_autoloader', 'autoloader'));

use plugin\struct\meta\SchemaBuilder;
use plugin\struct\meta\Schema;

/**
 * Class SchemaData for testing
 *
 * Makes protected methods accessible and avoids database initialization
 *
 * @package plugin\struct\test
 */
class SchemaDataDB extends \plugin\struct\meta\SchemaData {

    public function __construct($table, $page, $ts) {
        // we do intialization by parent here, because we don't need the whole database behind the class
        parent::__construct($table, $page, $ts);
    }

    public function setCorrectTimestamp($ts = null) {
        parent::setCorrectTimestamp($ts);
    }

    public function getDataFromDB() {
        return parent::getDataFromDB();
    }

}

/**
 * Tests to the DB for the struct plugin
 *
 * @group plugin_struct
 * @group plugins
 *
 */
class schemaDataDB_struct_test extends \DokuWikiTest {

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
        $testdata['new']['new1']['label'] = 'testcolumn';
        $testdata['new']['new1']['ismulti'] = 0;
        $testdata['new']['new1']['config'] = '{"prefix": "", "postfix": ""}';
        $testdata['new']['new1']['class'] = 'Text';
        $testdata['new']['new2']['sort'] = 40;
        $testdata['new']['new2']['label'] = 'testMulitColumn';
        $testdata['new']['new2']['ismulti'] = 1;
        $testdata['new']['new2']['config'] = '{"prefix": "", "postfix": ""}';
        $testdata['new']['new2']['class'] = 'Text';

        $testname = 'testTable';
        $testname = Schema::cleanTableName($testname);

        $builder = new SchemaBuilder($testname, $testdata);
        $builder->build();

        // revision 1
        $this->sqlite->query("INSERT INTO data_testtable (pid, rev, col1) VALUES (?,?,?)", array('testpage', 123, 'value1',));
        $this->sqlite->query("INSERT INTO multivals (tbl, colref, pid, rev, row, value) VALUES (?,?,?,?,?,?)",
                       array('testtable',2,'testpage',123,1,'value2.1',));
        $this->sqlite->query("INSERT INTO multivals (tbl, colref, pid, rev, row, value) VALUES (?,?,?,?,?,?)",
                       array('testtable',2,'testpage',123,2,'value2.2',));


        // revision 2
        $this->sqlite->query("INSERT INTO data_testtable (pid, rev, col1) VALUES (?,?,?)", array('testpage', 789, 'value1a',));
        $this->sqlite->query("INSERT INTO multivals (tbl, colref, pid, rev, row, value) VALUES (?,?,?,?,?,?)",
                       array('testtable',2,'testpage',789,1,'value2.1a',));
        $this->sqlite->query("INSERT INTO multivals (tbl, colref, pid, rev, row, value) VALUES (?,?,?,?,?,?)",
                       array('testtable',2,'testpage',789,2,'value2.2a',));
    }

    public function tearDown() {
        parent::tearDown();

        $res = $this->sqlite->query("SELECT name FROM sqlite_master WHERE type='table'");
        $tableNames = $this->sqlite->res2arr($res);
        $tableNames = array_map(function ($value) { return $value['name'];},$tableNames);
        $this->sqlite->res_close($res);

        foreach ($tableNames as $tableName) {
            if ($tableName == 'opts') continue;
            $this->sqlite->query('DROP TABLE ?;', $tableName);
        }

        $this->sqlite->query("CREATE TABLE schema_assignments ( assign NOT NULL, tbl NOT NULL, PRIMARY KEY(assign, tbl) );");
        $this->sqlite->query("CREATE TABLE schema_cols ( sid INTEGER REFERENCES schemas (id), colref INTEGER NOT NULL, enabled BOOLEAN DEFAULT 1, tid INTEGER REFERENCES types (id), sort INTEGER NOT NULL, PRIMARY KEY ( sid, colref) )");
        $this->sqlite->query("CREATE TABLE schemas ( id INTEGER PRIMARY KEY AUTOINCREMENT, tbl NOT NULL, ts INT NOT NULL, chksum DEFAULT '' )");
        $this->sqlite->query("CREATE TABLE sqlite_sequence(name,seq)");
        $this->sqlite->query("CREATE TABLE types ( id INTEGER PRIMARY KEY AUTOINCREMENT, class NOT NULL, ismulti BOOLEAN DEFAULT 0, label DEFAULT '', config DEFAULT '' )");
        $this->sqlite->query("CREATE TABLE multivals ( tbl NOT NULL, colref INTEGER NOT NULL, pid NOT NULL, rev INTEGER NOT NULL, row INTEGER NOT NULL, value, PRIMARY KEY(tbl, colref, pid, rev, row) )");
    }

    public function test_getDataFromDB_currentRev() {

        // act
        $schemaData = new SchemaDataDB('testtable','testpage', "");
        $schemaData->setCorrectTimestamp();
        $actual_data =  $schemaData->getDataFromDB();

        $expected_data = array(
            array(
                'col1' => 'value1a',
                'col2' => 'value2.1a',
            ),
            array(
                'col1' => 'value1a',
                'col2' => 'value2.2a',
            ),
        );


        $this->assertEquals($expected_data, $actual_data , '');
    }

    public function test_getDataFromDB_oldRev() {

        // act
        $schemaData = new SchemaDataDB('testtable','testpage','');
        $schemaData->setCorrectTimestamp(200);
        $actual_data = $schemaData->getDataFromDB();

        $expected_data = array(
            array(
                'col1' => 'value1',
                'col2' => 'value2.1',
            ),
            array(
                'col1' => 'value1',
                'col2' => 'value2.2',
            ),
        );

        $this->assertEquals($expected_data, $actual_data , '');
    }

    public function test_getData_currentRev() {

        // act
        $schemaData = new SchemaDataDB('testtable','testpage', "");
        $schemaData->setCorrectTimestamp();
        $actual_data = $schemaData->getData();

        $expected_data = array(
            'testMulitColumn' => array('value2.1a', 'value2.2a'),
            'testcolumn' => 'value1a',
        );

        // assert
        $this->assertEquals($expected_data, $actual_data , '');
    }

    public function test_getData_oldRev() {

        // act
        $schemaData = new SchemaDataDB('testtable','testpage','');
        $schemaData->setCorrectTimestamp(200);
        $actual_data = $schemaData->getData();

        $expected_data = array(
            'testMulitColumn' => array('value2.1', 'value2.2'),
            'testcolumn' => 'value1',
        );

        // assert
        $this->assertEquals($expected_data, $actual_data , '');
    }

    public function test_saveData() {
        // arrange
        $testdata = array(
            'testcolumn' => 'value1_saved',
            'testMulitColumn' => array(
                "value2.1_saved",
                "value2.2_saved",
                "value2.3_saved",
            )
        );

        // act
        $schemaData = new \plugin\struct\meta\SchemaData('testtable','testpage', "");
        $result = $schemaData->saveData($testdata);

        // assert
        $res = $this->sqlite->query("SELECT pid, col1, col2 FROM data_testtable WHERE pid = ? ORDER BY rev DESC LIMIT 1",array('testpage'));
        $actual_saved_single = $this->sqlite->res2row($res);
        $expected_saved_single = array(
            'pid' => 'testpage',
            'col1' => 'value1_saved',
            'col2' => ''
        );

        $res = $this->sqlite->query("SELECT colref, row, value FROM multivals WHERE pid = ? AND tbl = ? ORDER BY rev DESC LIMIT 3",array('testpage', 'testtable'));
        $actual_saved_multi = $this->sqlite->res2arr($res);
        $expected_saved_multi = array(
            array(
                'colref' => '2',
                'row' => '1',
                'value' => "value2.1_saved"
            ),
            array(
                'colref' => '2',
                'row' => '2',
                'value' => "value2.2_saved"
            ),
            array(
                'colref' => '2',
                'row' => '3',
                'value' => "value2.3_saved"
            )
        );


        $this->assertTrue($result, 'should be true on success');
        $this->assertEquals($expected_saved_single, $actual_saved_single,'single value fields');
        $this->assertEquals($expected_saved_multi, $actual_saved_multi,'multi value fields');
    }
}
