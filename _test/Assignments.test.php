<?php

namespace plugin\struct\test;

// we don't have the auto loader here
spl_autoload_register(array('action_plugin_struct_autoloader', 'autoloader'));

class Assignments extends \plugin\struct\meta\Assignments {

    public function __construct() {

        $this->assignments = array(
            array('assign' => 'a:single:page', 'tbl' => 'singlepage'),
            array('assign' => 'the:namespace:*', 'tbl' => 'singlens'),
            array('assign' => 'another:namespace:**', 'tbl' => 'deepns')
        );

    }

}

/**
 * Tests for the building of SQL-Queries for the struct plugin
 *
 * @group plugin_struct
 * @group plugins
 *
 */
class Assignments_struct_test extends \DokuWikiTest {

    protected $pluginsEnabled = array('struct',);

    public function test_pagematching() {

        $ass = new Assignments();

        $this->assertEquals(array(), $ass->getPageAssignments('foo'));
        $this->assertEquals(array(), $ass->getPageAssignments('a:single'));
        $this->assertEquals(array('singlepage'), $ass->getPageAssignments('a:single:page'));

        $this->assertEquals(array(), $ass->getPageAssignments('the:foo'));
        $this->assertEquals(array('singlens'), $ass->getPageAssignments('the:namespace:foo'));
        $this->assertEquals(array(), $ass->getPageAssignments('the:namespace:foo:bar'));

        $this->assertEquals(array(), $ass->getPageAssignments('another:foo'));
        $this->assertEquals(array('deepns'), $ass->getPageAssignments('another:namespace:foo'));
        $this->assertEquals(array('deepns'), $ass->getPageAssignments('another:namespace:foo:bar'));
        $this->assertEquals(array('deepns'), $ass->getPageAssignments('another:namespace:foo:bar:baz'));
    }
}
