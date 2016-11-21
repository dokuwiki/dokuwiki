<?php

namespace dokuwiki\plugin\struct\test;

use dokuwiki\plugin\struct\meta;

/**
 * Tests for the building of SQL-Queries for the struct plugin
 *
 * @group plugin_struct
 * @group plugins
 *
 */
class AccessTableDataReplacement_struct_test extends StructTest {

    /** @var array alway enable the needed plugins */
    protected $pluginsEnabled = array('struct', 'sqlite');

    public function setUp() {
        parent::setUp();
        $schemafoo = array();
        $schemafoo['new']['new1']['label'] = 'pages';
        $schemafoo['new']['new1']['ismulti'] = 1;
        $schemafoo['new']['new1']['class'] = 'Page';
        $schemafoo['new']['new1']['isenabled'] = '1';

        $schemabar['new']['new2']['label'] = 'data';
        $schemabar['new']['new2']['ismulti'] = 0;
        $schemabar['new']['new2']['class'] = 'Text';
        $schemabar['new']['new2']['isenabled'] = '1';

        $builder_foo = new meta\SchemaBuilder('foo', $schemafoo);
        $builder_foo->build();

        $builder_bar = new meta\SchemaBuilder('bar', $schemabar);
        $builder_bar->build();

        $as = mock\Assignments::getInstance();
        $as->assignPageSchema('start', 'foo');
        $as->assignPageSchema('no:data', 'foo');
        $as->assignPageSchema('page1', 'bar');
        $as->assignPageSchema('page2', 'bar');
        $as->assignPageSchema('page2', 'bar');


        $this->saveData(
            'start',
            'foo',
            array(
                'pages' => array('page1', 'page2')
            )
        );

        $this->saveData(
            'page1',
            'bar',
            array(
                'data' => 'data of page1'
            )
        );

        $this->saveData(
            'page2',
            'bar',
            array(
                'data' => 'data of page2'
            )
        );
    }

    public function test_simple() {
        global $INFO;
        $INFO['id'] = 'start';
        $lines = array(
            "schema    : bar",
            "cols      : %pageid%, data",
            "filter    : %pageid% = \$STRUCT.foo.pages$"
        );

        $configParser = new meta\ConfigParser($lines);
        $actual_config = $configParser->getConfig();

        $search = new meta\SearchConfig($actual_config);
        list(, $opts) = $search->getSQL();
        $result = $search->execute();

        $this->assertEquals(array('page1', 'page2'), $opts, '$STRUCT.table.col$ should not require table to be selected');
        $this->assertEquals('data of page1', $result[0][1]->getValue());
        $this->assertEquals('data of page2', $result[1][1]->getValue());
    }

    public function test_emptyfield() {
        global $ID;
        $ID = 'no:data';
        $lines = array(
            "schema    : bar",
            "cols      : %pageid%, data",
            "filter    : %pageid% = \$STRUCT.foo.pages$"
        );

        $configParser = new meta\ConfigParser($lines);
        $actual_config = $configParser->getConfig();

        $search = new meta\SearchConfig($actual_config);
        $result = $search->execute();

        $this->assertEquals(0, count($result), 'if no pages a given, then none should be shown');
    }

}
