<?php

namespace dokuwiki\plugin\struct\test;

use dokuwiki\plugin\struct\test\mock\AccessTable;
use dokuwiki\plugin\struct\test\mock\Dropdown;

/**
 * Testing the Dropdown Type
 *
 * @group plugin_struct
 * @group plugins
 */
class Type_Dropdown_struct_test extends StructTest {

    protected function preparePages() {
        $this->loadSchemaJSON('dropdowns');
        $this->saveData('test1', 'dropdowns', array('drop1' => '1', 'drop2' => '1', 'drop3' => 'John'));
        $this->saveData('test2', 'dropdowns', array('drop1' => '2', 'drop2' => '2', 'drop3' => 'Jane'));
        $this->saveData('test3', 'dropdowns', array('drop1' => '3', 'drop2' => '3', 'drop3' => 'Tarzan'));
    }


    public function test_data() {
        $this->preparePages();

        $access = AccessTable::byTableName('dropdowns', 'test1');
        $data = $access->getData();

        $this->assertEquals('John', $data[2]->getValue());
        $this->assertEquals('John', $data[2]->getRawValue());
        $this->assertEquals('John', $data[2]->getDisplayValue());

        $R = new \Doku_Renderer_xhtml();
        $data[2]->render($R, 'xhtml');
        $this->assertEquals('John', $R->doc);
    }


    public function test_getOptions() {
        // fixed values
        $dropdown = new Dropdown(
            array(
                'values' => 'John, Jane, Tarzan',
            ),
            'test',
            false,
            0
        );
        $expect = array(
            '' => '',
            'Jane' => 'Jane',
            'John' => 'John',
            'Tarzan' => 'Tarzan'
        );
        $this->assertEquals($expect, $dropdown->getOptions());
    }

}
