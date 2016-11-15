<?php

namespace dokuwiki\plugin\struct\test;

use dokuwiki\plugin\struct\meta;

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

    protected $data1 = array(
        'page' => 'wiki:syntax',
        'pages' => array('wiki:syntax', 'wiki:welcome'),
        'lookup' => 'page1',
        'lookups' => array('page1', 'page2'),
        'media' => 'wiki:logo.png',
        'medias' => array('wiki:logo.png'),
        'title' => 'wiki:syntax',
        'titles' => array('wiki:syntax', 'wiki:welcome')
    );

    protected $data2 = array(
        'page' => 'wiki:syntax#something',
        'pages' => array('wiki:syntax#something', 'wiki:welcome#something'),
        'lookup' => 'page1',
        'lookups' => array('page1', 'page2'),
        'media' => 'wiki:logo.png',
        'medias' => array('wiki:logo.png'),
        'title' => 'wiki:syntax#something',
        'titles' => array('wiki:syntax#something', 'wiki:welcome#something')
    );

    protected $empty = array(
        'page' => '',
        'pages' => array(),
        'lookup' => '',
        'lookups' => array(),
        'media' => '',
        'medias' => array(),
        'title' => '',
        'titles' => array()
    );

    public function setUp() {
        parent::setUp();
        $this->loadSchemaJSON('moves');

        $schemaData = meta\AccessTable::byTableName('moves', 'page1', time());
        $schemaData->saveData($this->data1);

        $schemaData = meta\AccessTable::byTableName('moves', 'page2', time());
        $schemaData->saveData($this->data2);
    }

    public function test_selfmove() {
        // fake move event
        $evdata = array('src_id' => 'page1', 'dst_id' => 'page3');
        $event = new \Doku_Event('PLUGIN_MOVE_PAGE_RENAME', $evdata);
        $evhandler = new \action_plugin_struct_move();
        $this->assertTrue($evhandler->handle_move($event, null));

        // old page should be gone
        $schemaData = meta\AccessTable::byTableName('moves', 'page1', 0);
        $this->assertEquals($this->empty, $schemaData->getDataArray());

        // new page should have adjusted data
        $data = $this->data1;
        $data['lookup'] = 'page3';
        $data['lookups'] = array('page3', 'page2');
        $schemaData = meta\AccessTable::byTableName('moves', 'page3', 0);
        $this->assertEquals($data, $schemaData->getDataArray());

        // other page should have adjusted lookups
        $data = $this->data2;
        $data['lookup'] = 'page3';
        $data['lookups'] = array('page3', 'page2');
        $schemaData = meta\AccessTable::byTableName('moves', 'page2', 0);
        $this->assertEquals($data, $schemaData->getDataArray());
    }

    public function test_pagemove() {
        // fake move event
        $evdata = array('src_id' => 'wiki:syntax', 'dst_id' => 'foobar');
        $event = new \Doku_Event('PLUGIN_MOVE_PAGE_RENAME', $evdata);
        $evhandler = new \action_plugin_struct_move();
        $this->assertTrue($evhandler->handle_move($event, null));

        $data = $this->data1;
        $data['page'] = 'foobar';
        $data['pages'] = array('foobar', 'wiki:welcome');
        $data['title'] = 'foobar';
        $data['titles'] = array('foobar', 'wiki:welcome');
        $schemaData = meta\AccessTable::byTableName('moves', 'page1', 0);
        $this->assertEquals($data, $schemaData->getDataArray());

        $data = $this->data2;
        $data['page'] = 'foobar#something';
        $data['pages'] = array('foobar#something', 'wiki:welcome#something');
        $data['title'] = 'foobar#something';
        $data['titles'] = array('foobar#something', 'wiki:welcome#something');
        $schemaData = meta\AccessTable::byTableName('moves', 'page2', 0);
        $this->assertEquals($data, $schemaData->getDataArray());
    }
}
