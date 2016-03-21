<?php

namespace plugin\struct\test;

use plugin\struct\meta;
use plugin\struct\test\mock\SearchConfig;

spl_autoload_register(array('action_plugin_struct_autoloader', 'autoloader'));

/**
 * @group plugin_struct
 * @group plugins
 *
 */
class SearchConfig_struct_test extends \DokuWikiTest {

    protected $pluginsEnabled = array('struct', 'sqlite');

    public function test_filtervars_simple() {
        global $ID;
        $ID = 'foo:bar:baz';

        $searchConfig = new SearchConfig(array());

        $this->assertEquals('foo:bar:baz', $searchConfig->applyFilterVars('$ID$'));
        $this->assertEquals('baz', $searchConfig->applyFilterVars('$PAGE$'));
        $this->assertEquals('foo:bar', $searchConfig->applyFilterVars('$NS$'));
        $this->assertEquals(date('Y-m-d'), $searchConfig->applyFilterVars('$TODAY$'));
        $this->assertEquals('', $searchConfig->applyFilterVars('$USER$'));
        $_SERVER['REMOTE_USER'] = 'user';
        $this->assertEquals('user', $searchConfig->applyFilterVars('$USER$'));

        $this->assertEquals('user baz', $searchConfig->applyFilterVars('$USER$ $PAGE$'));
        $this->assertEquals('$user', $searchConfig->applyFilterVars('$user'));

    }

    // FIXME test the struct ones
}
