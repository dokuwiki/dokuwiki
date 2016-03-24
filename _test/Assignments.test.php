<?php

namespace plugin\struct\test;

// we don't have the auto loader here
spl_autoload_register(array('action_plugin_struct_autoloader', 'autoloader'));

/**
 * @group plugin_struct
 * @group plugins
 *
 */
class Assignments_struct_test extends StructTest {

    public function test_patternmatching() {
        $ass = new mock\Assignments();

        $this->assertTrue($ass->matchPagePattern('some:ns:page', 'some:ns:page'));
        $this->assertTrue($ass->matchPagePattern('some:ns:*', 'some:ns:page'));
        $this->assertTrue($ass->matchPagePattern('some:**', 'some:ns:page'));
        $this->assertTrue($ass->matchPagePattern('**', 'some:ns:page'));

        $this->assertFalse($ass->matchPagePattern('some:ns:page', 'some:ns:other'));
        $this->assertFalse($ass->matchPagePattern('some:ns:*', 'some:ns:deep:other'));

        // some regexes
        $this->assertTrue($ass->matchPagePattern('/page/', 'somepagehere'));
        $this->assertFalse($ass->matchPagePattern('/:page/', 'somepagehere'));
        $this->assertTrue($ass->matchPagePattern('/:page/', 'some:pagehere'));
        $this->assertTrue($ass->matchPagePattern('/:page/', 'pagehere'));
    }

    /**
     * check clearing works
     */
    public function test_clear() {
        $ass = new mock\Assignments();
        $this->assertTrue($ass->clear());
        $ass->addPattern('foo', 'foo');
        $ass->assignPageSchema('foo', 'foo');
        $this->assertTrue($ass->clear());

        $this->assertEquals(array(), $ass->getAllPatterns());
        $this->assertEquals(array(), $ass->getPageAssignments('foo', true));
        $this->assertEquals(array(), $ass->getPageAssignments('foo', false));

        // old page is still known
        $this->assertEquals(array('foo' => array('foo' => false)), $ass->getPages());

        // now it's gone
        $ass->clear(true);
        $this->assertEquals(array(), $ass->getPages());
    }

    /**
     * basic usage
     */
    public function test_patternassigns() {
        $ass = new mock\Assignments();
        $ass->clear(true);

        $ass->addPattern('a:single:page', 'singlepage');
        $ass->addPattern('the:namespace:*', 'singlens');
        $ass->addPattern('another:namespace:**', 'deepns');

        $this->assertEquals(
            array(
                array(
                    'pattern' => 'a:single:page',
                    'tbl' => 'singlepage'
                ),
                array(
                    'pattern' => 'another:namespace:**',
                    'tbl' => 'deepns'
                ),
                array(
                    'pattern' => 'the:namespace:*',
                    'tbl' => 'singlens'
                )
            ),
            $ass->getAllPatterns()
        );

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

        $ass->removePattern('a:single:page', 'singlepage');
        $ass->removePattern('the:namespace:*', 'singlens');
        $ass->removePattern('another:namespace:**', 'deepns');

        $this->assertEquals(array(), $ass->getAllPatterns());
    }

    /**
     * Check reevaluation of patterns for a specific page works
     */
    public function test_pagereassign() {
        $ass = new mock\Assignments();
        $ass->clear(true);

        // no assignment
        $this->assertEquals(array(), $ass->getPageAssignments('wiki:syntax', false));

        // fake assign the page to some schema
        $ass->assignPageSchema('wiki:syntax', 'foo');
        $this->assertEquals(array('foo'), $ass->getPageAssignments('wiki:syntax', false));

        // reevaluate should deassign
        $ass->reevaluatePageAssignments('wiki:syntax');
        $this->assertEquals(array(), $ass->getPageAssignments('wiki:syntax', false));

        // add a pattern and deliberately deassign the page
        $ass->addPattern('wiki:*', 'foo');
        $ass->deassignPageSchema('wiki:syntax', 'foo');
        $this->assertEquals(array(), $ass->getPageAssignments('wiki:syntax', false));

        // reevaluate should assign
        $ass->reevaluatePageAssignments('wiki:syntax');
        $this->assertEquals(array('foo'), $ass->getPageAssignments('wiki:syntax', false));
    }

    /**
     * Check the direct page assignments
     */
    public function test_pageassign() {
        $ass = new mock\Assignments();
        $ass->clear(true);

        // no assignment
        $this->assertEquals(array(), $ass->getPageAssignments('wiki:syntax', false));

        // fake assign the page to some schema
        $ass->assignPageSchema('wiki:syntax', 'foo');
        $this->assertEquals(array('foo'), $ass->getPageAssignments('wiki:syntax', false));

        // removing any pattern of the same schema should recheck all existing assignments
        $ass->removePattern('anything', 'foo');
        $this->assertEquals(array(), $ass->getPageAssignments('wiki:syntax', false));

        // now the page is known to once have had data for that schema, a new pattern will reassign it
        $ass->addPattern('wiki:*', 'foo');
        $this->assertEquals(array('foo'), $ass->getPageAssignments('wiki:syntax', false));

        // adding another pattern
        $ass->addPattern('**', 'foo');
        $this->assertEquals(array('foo'), $ass->getPageAssignments('wiki:syntax', false));

        // removing one of the patterns, while the other still covers the same schema
        $ass->removePattern('wiki:*', 'foo');
        $this->assertEquals(array('foo'), $ass->getPageAssignments('wiki:syntax', false));

        // new pattern will also update all known struct pages
        $ass->addPattern('wiki:*', 'bar');
        $this->assertEquals(array('bar', 'foo'), $ass->getPageAssignments('wiki:syntax', false));

        // page should deassign again
        $ass->addPattern('*', 'baz');
        $ass->assignPageSchema('attoplevel', 'baz');
        $ass->assignPageSchema('attoplevel', 'foo');
        $ass->removePattern('*', 'baz');
        $ass->removePattern('**', 'foo');

        // check that all pages are known
        $expect = array(
            'attoplevel' => array(
                'baz' => false,
                'foo' => false
            ),
            'wiki:syntax' => array(
                'bar' => true,
                'foo' => false
            )
        );
        $this->assertEquals($expect, $ass->getPages());

        // limit to certain schema
        $expect = array(
            'attoplevel' => array(
                'baz' => false,
            ),
        );
        $this->assertEquals($expect, $ass->getPages('baz'));

        // show current assignments only
        $expect = array(
            'wiki:syntax' => array(
                'bar' => true,
            )
        );
        $this->assertEquals($expect, $ass->getPages(null, true));
    }
}
