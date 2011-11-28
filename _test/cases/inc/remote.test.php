<?php

require_once DOKU_INC . 'inc/init.php';

Mock::generate('Doku_Plugin_Controller');

class remote_plugin_testplugin extends DokuWiki_Remote_Plugin {
    function _getMethods() {
        return array(
            'method1' => array(
                'args' => array(),
                'return' => 'void'
            ),
            'method2' => array(
                'args' => array('string', 'int', 'bool'),
                'return' => array('string'),
            )
        );
    }
}


class remote_test extends UnitTestCase {

    var $originalConf;

    var $remote;

    function setUp() {
        global $plugin_controller;
        global $conf;
        parent::setUp();
        $pluginManager = new MockDoku_Plugin_Controller();
        $pluginManager->setReturnValue('getList', array('testplugin'));
        $pluginManager->setReturnValue('load', new remote_plugin_testplugin());
        $plugin_controller = $pluginManager;

        $this->originalConf = $conf;

        $this->remote = new RemoteAPI();
    }

    function tearDown() {
        global $conf;
        $conf = $this->originalConf;
    }

    function test_pluginMethods() {
        $methods = $this->remote->getPluginMethods();
        $this->assertEqual(array_keys($methods), array('plugin.testplugin.method1', 'plugin.testplugin.method2'));
    }

    function test_hasAccessSuccess() {
        global $conf;
        $conf['remote'] = 1;
        $this->assertTrue($this->remote->hasAccess());
    }

    function test_hasAccessFail() {
        global $conf;
        $conf['remote'] = 0;
        $this->assertFalse($this->remote->hasAccess());
    }

    function test_forceAccessSuccess() {
        global $conf;
        $conf['remote'] = 1;
        $this->remote->forceAccess(); // no exception should occur
    }

    function test_forceAccessFail() {
        global $conf;
        $conf['remote'] = 0;
        $this->expectException('RemoteException');
        $this->remote->forceAccess();
    }
}
