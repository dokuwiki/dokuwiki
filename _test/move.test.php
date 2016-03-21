<?php

namespace plugin\struct\test;

use plugin\struct\meta;

// we don't have the auto loader here
spl_autoload_register(array('action_plugin_struct_autoloader', 'autoloader'));

/**
 * Tests for the move plugin support of the struct plugin
 *
 * @group plugin_struct
 * @group plugins
 *
 * @covers action_plugin_struct_move
 *
 *
 */
class move_struct_test extends \DokuWikiTest {

    protected $pluginsEnabled = array('struct', 'sqlite');

    public function setUp() {
        parent::setUp();

        $schema = 'schema1';
        $sb = new meta\SchemaBuilder(
            $schema,
            array(
                'new' => array(
                    'new1' => array('label' => 'first', 'class' => 'Text', 'sort' => 10, 'ismulti' => 0, 'isenabled' => 1),
                    'new2' => array('label' => 'second', 'class' => 'Text', 'sort' => 20, 'ismulti' => 1, 'isenabled' => 1),
                    'new3' => array('label' => 'third', 'class' => 'Text', 'sort' => 30, 'ismulti' => 0, 'isenabled' => 1),
                    'new4' => array('label' => 'fourth', 'class' => 'Text', 'sort' => 40, 'ismulti' => 0, 'isenabled' => 1)
                )
            )
        );
        $sb->build();
    }

    public function tearDown() {
        parent::tearDown();

        /** @var \helper_plugin_struct_db $sqlite */
        $sqlite = plugin_load('helper', 'struct_db');
        $sqlite->resetDB();
    }

    public function test_move() {
        $data = array(
            'first' => 'data 1',
            'second' => array('data 2.1', 'data 2.2'),
            'third' => 'data 3',
            'fourth' => 'data 4',
        );
        $empty = array(
            'first' => '',
            'second' => array(),
            'third' => '',
            'fourth' => '',
        );

        // add initial data
        $schemaData = new meta\SchemaData('schema1', 'somepage', time());
        $schemaData->saveData($data);
        $this->assertEquals($data, $schemaData->getDataArray());

        // fake move event
        $evdata = array('src_id' => 'somepage', 'dst_id' => 'newpage');
        $event = new \Doku_Event('PLUGIN_MOVE_PAGE_RENAME', $evdata);
        $evhandler = new \action_plugin_struct_move();
        $this->assertTrue($evhandler->handle_move($event, null));

        // old page should be gone
        $schemaData = new meta\SchemaData('schema1', 'somepage', 0);
        $this->assertEquals($empty, $schemaData->getDataArray());

        // new page should have data
        $schemaData = new meta\SchemaData('schema1', 'newpage', 0);
        $this->assertEquals($data, $schemaData->getDataArray());
    }

}
