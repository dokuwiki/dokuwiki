<?php

namespace plugin\struct\test;

use plugin\struct\types\Page;

spl_autoload_register(array('action_plugin_struct_autoloader', 'autoloader'));

/**
 * Testing the User Type
 *
 * @group plugin_struct
 * @group plugins
 */
class Type_Page_struct_test extends \DokuWikiTest {

    protected $pluginsEnabled = array('struct', 'sqlite');

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
                'namespace' => '',
                'postfix' => '',
                'fullname' => true,
                'autocomplete' => array(
                    'mininput' => 2,
                    'maxresult' => 5,
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
                'namespace' => 'wiki',
                'postfix' => '',
                'fullname' => true,
                'autocomplete' => array(
                    'mininput' => 2,
                    'maxresult' => 5,
                ),
            )
        );

        $INPUT->set('search', 'ynt');
        $this->assertEquals(array(array('label'=>'syntax (wiki)', 'value'=>'syntax')), $page->handleAjax());
    }

    public function test_ajax_postfix() {
        global $INPUT;

        $page = new Page(
            array(
                'namespace' => '',
                'postfix' => 'iki',
                'fullname' => true,
                'autocomplete' => array(
                    'mininput' => 2,
                    'maxresult' => 5,
                ),
            )
        );

        $INPUT->set('search', 'oku');
        $this->assertEquals(array(array('label'=>'dokuwiki (wiki)', 'value'=>'wiki:dokuw')), $page->handleAjax());
    }

}
