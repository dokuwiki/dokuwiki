<?php

namespace dokuwiki\plugin\struct\test;

use dokuwiki\plugin\struct\meta;

/**
 * Tests for the Column object
 *
 * @group plugin_struct
 * @group plugins
 */
class column_struct_test extends StructTest {

    public function test_allTypes() {

        $expect = array(
            'Checkbox' => 'dokuwiki\\plugin\\struct\\types\\Checkbox',
            'Date' => 'dokuwiki\\plugin\\struct\\types\\Date',
            'DateTime' => 'dokuwiki\\plugin\\struct\\types\\DateTime',
            'Decimal' => 'dokuwiki\\plugin\\struct\\types\\Decimal',
            'Dropdown' => 'dokuwiki\\plugin\\struct\\types\\Dropdown',
            'Lookup' => 'dokuwiki\\plugin\\struct\\types\\Lookup',
            'Mail' => 'dokuwiki\\plugin\\struct\\types\\Mail',
            'Media' => 'dokuwiki\\plugin\\struct\\types\\Media',
            'Page' => 'dokuwiki\\plugin\\struct\\types\\Page',
            'Tag' => 'dokuwiki\\plugin\\struct\\types\\Tag',
            'Text' => 'dokuwiki\\plugin\\struct\\types\\Text',
            'Url' => 'dokuwiki\\plugin\\struct\\types\\Url',
            'User' => 'dokuwiki\\plugin\\struct\\types\\User',
            'Wiki' => 'dokuwiki\\plugin\\struct\\types\\Wiki',
        );

        $this->assertEquals($expect, meta\Column::allTypes(true));
    }

    public function test_extendedTypes() {

        $expect = array(
            'Checkbox' => 'dokuwiki\\plugin\\struct\\types\\Checkbox',
            'Date' => 'dokuwiki\\plugin\\struct\\types\\Date',
            'DateTime' => 'dokuwiki\\plugin\\struct\\types\\DateTime',
            'Decimal' => 'dokuwiki\\plugin\\struct\\types\\Decimal',
            'Dropdown' => 'dokuwiki\\plugin\\struct\\types\\Dropdown',
            'Lookup' => 'dokuwiki\\plugin\\struct\\types\\Lookup',
            'Mail' => 'dokuwiki\\plugin\\struct\\types\\Mail',
            'Media' => 'dokuwiki\\plugin\\struct\\types\\Media',
            'Page' => 'dokuwiki\\plugin\\struct\\types\\Page',
            'test' => 'some\\test\\class',
            'Tag' => 'dokuwiki\\plugin\\struct\\types\\Tag',
            'Text' => 'dokuwiki\\plugin\\struct\\types\\Text',
            'Url' => 'dokuwiki\\plugin\\struct\\types\\Url',
            'User' => 'dokuwiki\\plugin\\struct\\types\\User',
            'Wiki' => 'dokuwiki\\plugin\\struct\\types\\Wiki',
        );

        global $EVENT_HANDLER;
        $EVENT_HANDLER->register_hook('PLUGIN_STRUCT_TYPECLASS_INIT', 'BEFORE', $this, 'event');
        $this->assertEquals($expect, meta\Column::allTypes(true));
    }

    /**
     * Fake event that adds a new type to the list of types
     *
     * @param \Doku_Event $event
     * @param $param
     */
    public function event(\Doku_Event $event, $param) {
        $event->data['test'] = 'some\\test\\class';
    }

}
