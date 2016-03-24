<?php

namespace plugin\struct\test;

use plugin\struct\meta\Schema;
use plugin\struct\types\Tag;

spl_autoload_register(array('action_plugin_struct_autoloader', 'autoloader'));

/**
 * @group plugin_struct
 * @group plugins
 */
class Type_Tag_struct_test extends StructTest {

    public function setUp() {
        parent::setUp();
        $this->loadSchemaJSON('tag');

        $this->waitForTick();
        $this->saveData('page1', 'tag', array('tag' => 'Aragorn', 'tags'=>array('Faramir', 'Gollum')));
        $this->saveData('page2', 'tag', array('tag' => 'Eldarion', 'tags'=>array('Saruman', 'Arwen')));
        $this->waitForTick();
        $this->saveData('page1', 'tag', array('tag' => 'Treebeard', 'tags'=>array('Frodo', 'Arwen')));
    }


    public function test_autocomplete() {
        global $INPUT;
        $schema = new Schema('tag');

        // search tag field, should not find Aragon because tag is not in current revision
        $INPUT->set('search', 'ar');
        $tag = $schema->findColumn('tag')->getType();
        $return = $tag->handleAjax();
        $expect = array(
            array('label' => 'Eldarion', 'value' => 'Eldarion'),
            array('label' => 'Treebeard', 'value' => 'Treebeard'),
        );
        $this->assertEquals($expect, $return);

        // multi value
        $INPUT->set('search', 'ar');
        $tag = $schema->findColumn('tags')->getType();
        $return = $tag->handleAjax();
        $expect = array(
            array('label' => 'Arwen', 'value' => 'Arwen'),
            array('label' => 'Saruman', 'value' => 'Saruman'),
        );
        $this->assertEquals($expect, $return);

    }
}
