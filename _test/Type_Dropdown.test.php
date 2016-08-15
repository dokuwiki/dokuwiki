<?php

namespace dokuwiki\plugin\struct\test;

use dokuwiki\plugin\struct\meta\Title;
use dokuwiki\plugin\struct\test\mock\AccessTable;
use dokuwiki\plugin\struct\test\mock\Dropdown;

/**
 * Testing the Dropdown Type
 *
 * @group plugin_struct
 * @group plugins
 */
class Type_Dropdown_struct_test extends StructTest {


    protected function prepareLookup() {
        saveWikiText('title1', 'test', 'test');
        $title = new Title('title1');
        $title->setTitle('This is a title');

        saveWikiText('title2', 'test', 'test');
        $title = new Title('title2');
        $title->setTitle('This is a 2nd title');

        saveWikiText('title3', 'test', 'test');
        $title = new Title('title3');
        $title->setTitle('Another Title');

        $this->loadSchemaJSON('pageschema', '', 0, true);
        $access = AccessTable::byTableName('pageschema', 0);
        $access->saveData(
            array(
                'singlepage' => 'title1',
                'multipage' => array('title1'),
                'singletitle' => 'title1',
                'multititle' => array('title1'),
            )
        );
        $access = AccessTable::byTableName('pageschema', 0);
        $access->saveData(
            array(
                'singlepage' => 'title2',
                'multipage' => array('title2'),
                'singletitle' => 'title2',
                'multititle' => array('title2'),
            )
        );
        $access = AccessTable::byTableName('pageschema', 0);
        $access->saveData(
            array(
                'singlepage' => 'title3',
                'multipage' => array('title3'),
                'singletitle' => 'title3',
                'multititle' => array('title3'),
            )
        );
    }

    protected function preparePages() {
        $this->loadSchemaJSON('dropdowns');
        $this->saveData('test1', 'dropdowns', array('drop1' => '1', 'drop2' => '1', 'drop3' => 'John'));
        $this->saveData('test2', 'dropdowns', array('drop1' => '2', 'drop2' => '2', 'drop3' => 'Jane'));
        $this->saveData('test3', 'dropdowns', array('drop1' => '3', 'drop2' => '3', 'drop3' => 'Tarzan'));
    }


    public function test_data() {
        $this->prepareLookup();
        $this->preparePages();

        $access = AccessTable::byTableName('dropdowns', 'test1');
        $data = $access->getData();

        $this->assertEquals('["1","[\\"title1\\",\\"This is a title\\"]"]', $data[0]->getValue());
        $this->assertEquals('["1","title1"]', $data[1]->getValue());
        $this->assertEquals('John', $data[2]->getValue());

        $this->assertEquals('1', $data[0]->getRawValue());
        $this->assertEquals('1', $data[1]->getRawValue());
        $this->assertEquals('John', $data[2]->getRawValue());

        $this->assertEquals('This is a title', $data[0]->getDisplayValue());
        $this->assertEquals('title1', $data[1]->getDisplayValue());
        $this->assertEquals('John', $data[2]->getDisplayValue());
    }

    public function test_getOptions() {
        $this->prepareLookup();

        // lookup with titles
        $dropdown = new Dropdown(
            array(
                'schema' => 'pageschema',
                'field' => 'singletitle'
            ),
            'test',
            false,
            0
        );
        $expect = array(
            '' => '',
            3 => 'Another Title',
            2 => 'This is a 2nd title',
            1 => 'This is a title',
        );
        $this->assertEquals($expect, $dropdown->getOptions());

        // lookup with pages
        $dropdown = new Dropdown(
            array(
                'schema' => 'pageschema',
                'field' => 'singlepage'
            ),
            'test',
            false,
            0
        );
        $expect = array(
            '' => '',
            1 => 'title1',
            2 => 'title2',
            3 => 'title3',
        );
        $this->assertEquals($expect, $dropdown->getOptions());

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
