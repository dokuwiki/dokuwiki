<?php

use plugin\struct\meta\SchemaBuilder;
use plugin\struct\meta\Schema;

/**
 * Tests for the class action_plugin_magicmatcher_oldrevisions of the magicmatcher plugin
 *
 * @group plugin_struct
 * @group plugins
 *
 */
class schemaBuilder_struct_test extends DokuWikiTest {

    protected $pluginsEnabled = array('struct', 'sqlite');

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();

        /** @var helper_plugin_sqlite $sqlite */
        $sqlite = plugin_load('helper', 'struct_db');
        $sqlite = $sqlite->getDB();

        $res = $sqlite->query("SELECT name FROM sqlite_master WHERE type='table'");
        $tableNames = $sqlite->res2arr($res);
        $tableNames = array_map(function ($value) { return $value['name'];},$tableNames);
        $sqlite->res_close($res);

        foreach ($tableNames as $tableName) {
            if ($tableName == 'opts') continue;
            $sqlite->query('DROP TABLE ?;', $tableName);
        }


        $sqlite->query("CREATE TABLE schema_assignments ( assign NOT NULL, tbl NOT NULL, PRIMARY KEY(assign, tbl) );");
        $sqlite->query("CREATE TABLE schema_cols ( sid INTEGER REFERENCES schemas (id), colref INTEGER NOT NULL, enabled BOOLEAN DEFAULT 1, tid INTEGER REFERENCES types (id), sort INTEGER NOT NULL, PRIMARY KEY ( sid, colref) )");
        $sqlite->query("CREATE TABLE schemas ( id INTEGER PRIMARY KEY AUTOINCREMENT, tbl NOT NULL, ts INT NOT NULL, chksum DEFAULT '' )");
        $sqlite->query("CREATE TABLE sqlite_sequence(name,seq)");
        $sqlite->query("CREATE TABLE types ( id INTEGER PRIMARY KEY AUTOINCREMENT, class NOT NULL, ismulti BOOLEAN DEFAULT 0, label DEFAULT '', config DEFAULT '' )");
        $sqlite->query("CREATE TABLE multivals ( tbl NOT NULL, colref INTEGER NOT NULL, pid NOT NULL, rev INTEGER NOT NULL, row INTEGER NOT NULL, value, PRIMARY KEY(tbl, colref, pid, rev, row) )");
    }

    /**
     *
     */
    public function test_build_new() {

        // arrange
        $testdata = array();
        $testdata['new']['new1']['sort'] = 70;
        $testdata['new']['new1']['label'] = 'testcolumn';
        $testdata['new']['new1']['ismulti'] = 0;
        $testdata['new']['new1']['config'] = '{"prefix": "", "postfix": ""}';
        $testdata['new']['new1']['class'] = 'Text';
        $testdata['new']['new2']['sort'] = 40;
        $testdata['new']['new2']['label'] = 'testMulitColumn';
        $testdata['new']['new2']['ismulti'] = 1;
        $testdata['new']['new2']['config'] = '{"prefix": "pre", "postfix": "post"}';
        $testdata['new']['new2']['class'] = 'Text';

        $testname = 'testTable';
        $testname = Schema::cleanTableName($testname);

        // act
        $builder = new SchemaBuilder($testname, $testdata);
        $result = $builder->build();

        /** @var helper_plugin_sqlite $sqlite */
        $sqlite = plugin_load('helper', 'struct_db');
        $sqlite = $sqlite->getDB();

        $res = $sqlite->query("SELECT sql FROM sqlite_master WHERE type='table' AND name=?", 'data_' . $testname);
        $tableSQL = $sqlite->res2single($res);
        $sqlite->res_close($res);
        $expected_tableSQL = "CREATE TABLE data_testtable (
                    pid NOT NULL,
                    rev INTEGER NOT NULL, col1 DEFAULT '', col2 DEFAULT '',
                    PRIMARY KEY(pid, rev)
                )";

        $res = $sqlite->query("SELECT * FROM types");
        $actual_types = $sqlite->res2arr($res);
        $sqlite->res_close($res);
        $expected_types = array(
            array(
                'id' => "1",
                'class' => 'Text',
                'ismulti' => "0",
                'label' => "testcolumn",
                'config' => '{"prefix": "", "postfix": ""}'
            ),
            array(
                'id' => "2",
                'class' => 'Text',
                'ismulti' => "1",
                'label' => "testMulitColumn",
                'config' => '{"prefix": "pre", "postfix": "post"}'
            )
        );

        $res = $sqlite->query("SELECT * FROM schema_cols");
        $actual_cols = $sqlite->res2arr($res);
        $sqlite->res_close($res);
        $expected_cols = array(
            array(
                'sid' => "1",
                'colref' => "1",
                'enabled' => "1",
                'tid' => "1",
                'sort' => "70"
            ),
            array(
                'sid' => "1",
                'colref' => "2",
                'enabled' => "1",
                'tid' => "2",
                'sort' => "40"
            )
        );

        $res = $sqlite->query("SELECT * FROM schemas");
        $actual_schema = $sqlite->res2row($res);
        $sqlite->res_close($res);

        $this->assertSame($result, '1');
        $this->assertEquals($tableSQL, $expected_tableSQL);
        $this->assertEquals($actual_types, $expected_types);
        $this->assertEquals($actual_cols, $expected_cols);
        $this->assertEquals($actual_schema['id'], '1');
        $this->assertEquals($actual_schema['tbl'], $testname);
        $this->assertEquals($actual_schema['chksum'], '');
        $this->assertTrue((int)$actual_schema['ts'] > 0, 'timestamp should be larger than 0');
    }


    public function test_build_update() {

        // arrange
        $initialdata = array();
        $initialdata['new']['new1']['sort'] = 70;
        $initialdata['new']['new1']['label'] = 'testcolumn';
        $initialdata['new']['new1']['ismulti'] = 0;
        $initialdata['new']['new1']['config'] = '{"prefix": "", "postfix": ""}';
        $initialdata['new']['new1']['class'] = 'Text';

        $testname = 'testTable';
        $testname = Schema::cleanTableName($testname);

        $builder = new SchemaBuilder($testname, $initialdata);
        $result = $builder->build();
        $this->assertSame($result, '1', 'Prerequiste setup  in order to have basis which to change during act');

        $updatedata = array();
        $updatedata['id'] = "1";
        $updatedata['cols']['1']['sort'] = 65;
        $updatedata['cols']['1']['label'] = 'testColumn';
        $updatedata['cols']['1']['ismulti'] = 1;
        $updatedata['cols']['1']['config'] = '{"prefix": "pre", "postfix": "fix"}';
        $updatedata['cols']['1']['class'] = 'Text';

        // act
        $builder = new SchemaBuilder($testname, $updatedata);
        $result = $builder->build();


        /** @var helper_plugin_sqlite $sqlite */
        $sqlite = plugin_load('helper', 'struct_db');
        $sqlite = $sqlite->getDB();

        $res = $sqlite->query("SELECT * FROM types");
        $actual_types = $sqlite->res2arr($res);
        $sqlite->res_close($res);
        $expected_types = array(
            array(
                'id' => "1",
                'class' => 'Text',
                'ismulti' => "0",
                'label' => "testcolumn",
                'config' => '{"prefix": "", "postfix": ""}'
            ),
            array(
                'id' => "2",
                'class' => 'Text',
                'ismulti' => "1",
                'label' => "testColumn",
                'config' => '{"prefix": "pre", "postfix": "fix"}'
            )
        );

        // assert
        $this->assertSame($result, '2');
        $this->assertEquals($actual_types, $expected_types);

    }
}
