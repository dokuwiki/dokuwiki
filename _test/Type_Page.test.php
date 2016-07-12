<?php

namespace dokuwiki\plugin\struct\test;

use dokuwiki\plugin\struct\types\Page;

/**
 * Testing the Page Type
 *
 * @group plugin_struct
 * @group plugins
 */
class Type_Page_struct_test extends StructTest {

    public function setUp() {
        parent::setUp();

        saveWikiText('syntax', 'dummy', 'test');

        // make sure the search index is initialized
        idx_addPage('wiki:syntax');
        idx_addPage('syntax');
        idx_addPage('wiki:welcome');
        idx_addPage('wiki:dokuwiki');
    }

    public function test_ajax_default() {
        global $INPUT;

        $page = new Page(
            array(
                'autocomplete' => array(
                    'mininput' => 2,
                    'maxresult' => 5,
                    'namespace' => '',
                    'postfix' => '',
                ),
            )
        );

        $INPUT->set('search', 'syntax');
        $this->assertEquals(
            array(
                array('label' => 'syntax', 'value' => 'syntax'),
                array('label' => 'syntax (wiki)', 'value' => 'wiki:syntax')
            ), $page->handleAjax()
        );

        $INPUT->set('search', 'ynt');
        $this->assertEquals(
            array(
                array('label' => 'syntax', 'value' => 'syntax'),
                array('label' => 'syntax (wiki)', 'value' => 'wiki:syntax')
            ), $page->handleAjax()
        );

        $INPUT->set('search', 's'); // under mininput
        $this->assertEquals(array(), $page->handleAjax());
    }

    public function test_ajax_namespace() {
        global $INPUT;

        $page = new Page(
            array(
                'autocomplete' => array(
                    'mininput' => 2,
                    'maxresult' => 5,
                    'namespace' => 'wiki',
                    'postfix' => '',
                ),
            )
        );

        $INPUT->set('search', 'ynt');
        $this->assertEquals(array(array('label' => 'syntax (wiki)', 'value' => 'wiki:syntax')), $page->handleAjax());
    }

    public function test_ajax_postfix() {
        global $INPUT;

        $page = new Page(
            array(
                'autocomplete' => array(
                    'mininput' => 2,
                    'maxresult' => 5,
                    'namespace' => '',
                    'postfix' => 'iki',
                ),
            )
        );

        $INPUT->set('search', 'oku');
        $this->assertEquals(array(array('label' => 'dokuwiki (wiki)', 'value' => 'wiki:dokuwiki')), $page->handleAjax());
    }

}
