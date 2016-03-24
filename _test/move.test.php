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
class move_struct_test extends StructTest {


    public function setUp() {
        parent::setUp();
        $this->loadSchemaJSON('schema1');
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
