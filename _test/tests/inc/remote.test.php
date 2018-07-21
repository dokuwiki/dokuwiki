<?php

use dokuwiki\Extension\AuthPlugin;
use dokuwiki\Extension\RemotePlugin;
use dokuwiki\Remote\Api;

class MockAuth extends AuthPlugin {
    function isCaseSensitive() { return true; }
}

class RemoteAPICoreTest {

    function __getRemoteInfo() {
        return array(
            'wiki.stringTestMethod' => array(
                'args' => array(),
                'return' => 'string',
                'doc' => 'Test method',
                'name' => 'stringTestMethod',
            ), 'wiki.intTestMethod' => array(
                'args' => array(),
                'return' => 'int',
                'doc' => 'Test method',
                'name' => 'intTestMethod',
            ), 'wiki.floatTestMethod' => array(
                'args' => array(),
                'return' => 'float',
                'doc' => 'Test method',
                'name' => 'floatTestMethod',
            ), 'wiki.dateTestMethod' => array(
                'args' => array(),
                'return' => 'date',
                'doc' => 'Test method',
                'name' => 'dateTestMethod',
            ), 'wiki.fileTestMethod' => array(
                'args' => array(),
                'return' => 'file',
                'doc' => 'Test method',
                'name' => 'fileTestMethod',
            ), 'wiki.voidTestMethod' => array(
                'args' => array(),
                'return' => 'void',
                'doc' => 'Test method',
                'name' => 'voidTestMethod',
            ),  'wiki.oneStringArgMethod' => array(
                'args' => array('string'),
                'return' => 'string',
                'doc' => 'Test method',
                'name' => 'oneStringArgMethod',
            ), 'wiki.twoArgMethod' => array(
                'args' => array('string', 'int'),
                'return' => 'array',
                'doc' => 'Test method',
                'name' => 'twoArgMethod',
            ), 'wiki.twoArgWithDefaultArg' => array(
                'args' => array('string', 'string'),
                'return' => 'string',
                'doc' => 'Test method',
                'name' => 'twoArgWithDefaultArg',
            ), 'wiki.publicCall' => array(
                'args' => array(),
                'return' => 'boolean',
                'doc' => 'testing for public access',
                'name' => 'publicCall',
                'public' => 1
            )
        );
    }
    function stringTestMethod() { return 'success'; }
    function intTestMethod() { return 42; }
    function floatTestMethod() { return 3.14159265; }
    function dateTestMethod() { return 2623452346; }
    function fileTestMethod() { return 'file content'; }
    function voidTestMethod() { return null; }
    function oneStringArgMethod($arg) {return $arg; }
    function twoArgMethod($string, $int) { return array($string, $int); }
    function twoArgWithDefaultArg($string1, $string2 = 'default') { return array($string1, $string2); }
    function publicCall() {return true;}

}


class remote_test extends DokuWikiTest {

    protected $userinfo;

    protected $pluginsEnabled = ['testing'];

    /** @var  Api */
    protected $remote;

    function setUp() {
        parent::setUp();
        global $plugin_controller;
        global $conf;
        global $USERINFO;
        global $auth;

        parent::setUp();

        $conf['remote'] = 1;
        $conf['remoteuser'] = '!!not set!!';
        $conf['useacl'] = 0;

        $this->userinfo = $USERINFO;
        $this->remote = new Api();

        $auth = new MockAuth();
    }

    function tearDown() {
        global $USERINFO;
        $USERINFO = $this->userinfo;

    }

    function test_pluginMethods() {
        $methods = $this->remote->getPluginMethods();
        $actual = array_keys($methods);
        $expect = array(
            'plugin.testing_manual.method1',
            'plugin.testing_manual.method2',
            'plugin.testing_manual.methodString',
            'plugin.testing_manual.method2ext',
            'plugin.testing_manual.publicCall',

            'plugin.testing_auto.commented'
        );
        foreach($expect as $e) {
            $this->assertContains($e, $actual);
        }
    }

    function test_pluginDescriptors() {
        $methods = $this->remote->getPluginMethods();
        $this->assertEquals(array('string','int','bool','string'), $methods['plugin.testing_auto.commented']['args']);
        $this->assertEquals('array', $methods['plugin.testing_auto.commented']['return']);
        $this->assertEquals(0, $methods['plugin.testing_auto.commented']['public']);
        $this->assertContains('This is a dummy method', $methods['plugin.testing_auto.commented']['doc']);
        $this->assertContains('string $str some more parameter description', $methods['plugin.testing_auto.commented']['doc']);
    }

    function test_hasAccessSuccess() {
        global $conf;
        $conf['remoteuser'] = '';
        $this->assertTrue($this->remote->hasAccess());
    }

    /**
     * @expectedException dokuwiki\Remote\AccessDeniedException
     */
    function test_hasAccessFail() {
        global $conf;
        $conf['remote'] = 0;
        // the hasAccess() should throw a Exception to keep the same semantics with xmlrpc.php.
        // because the user(xmlrpc) check remote before .--> (!$conf['remote']) die('XML-RPC server not enabled.');
        // so it must be a Exception when get here.
        $this->remote->hasAccess();
    }

    function test_hasAccessFailAcl() {
        global $conf;
        $conf['useacl'] = 1;
        $this->assertFalse($this->remote->hasAccess());
    }

    function test_hasAccessSuccessAclEmptyRemoteUser() {
        global $conf;
        $conf['useacl'] = 1;
        $conf['remoteuser'] = '';

        $this->assertTrue($this->remote->hasAccess());
    }

    function test_hasAccessSuccessAcl() {
        global $conf;
        global $USERINFO;
        $conf['useacl'] = 1;
        $conf['remoteuser'] = '@grp,@grp2';
        $USERINFO['grps'] = array('grp');
        $this->assertTrue($this->remote->hasAccess());
    }

    function test_hasAccessFailAcl2() {
        global $conf;
        global $USERINFO;
        $conf['useacl'] = 1;
        $conf['remoteuser'] = '@grp';
        $USERINFO['grps'] = array('grp1');

        $this->assertFalse($this->remote->hasAccess());
    }


    function test_forceAccessSuccess() {
        global $conf;
        $conf['remote'] = 1;
        $conf['remoteuser'] = '';
        $this->remote->forceAccess(); // no exception should occur
        $this->assertTrue(true); // avoid being marked as risky for having no assertion
    }

    /**
     * @expectedException dokuwiki\Remote\RemoteException
     */
    function test_forceAccessFail() {
        global $conf;
        $conf['remote'] = 0;
        $this->remote->forceAccess();
    }

    function test_generalCoreFunctionWithoutArguments() {
        global $conf;
        global $USERINFO;
        $conf['remote'] = 1;
        $conf['remoteuser'] = '';
        $conf['useacl'] = 1;
        $USERINFO['grps'] = array('grp');
        $remoteApi = new Api();
        $remoteApi->getCoreMethods(new RemoteAPICoreTest());

        $this->assertEquals($remoteApi->call('wiki.stringTestMethod'), 'success');
        $this->assertEquals($remoteApi->call('wiki.intTestMethod'), 42);
        $this->assertEquals($remoteApi->call('wiki.floatTestMethod'), 3.14159265);
        $this->assertEquals($remoteApi->call('wiki.dateTestMethod'), 2623452346);
        $this->assertEquals($remoteApi->call('wiki.fileTestMethod'), 'file content');
        $this->assertEquals($remoteApi->call('wiki.voidTestMethod'), null);
    }

    /**
     * @expectedException dokuwiki\Remote\RemoteException
     */
    function test_generalCoreFunctionOnArgumentMismatch() {
        global $conf;
        $conf['remote'] = 1;
        $remoteApi = new Api();
        $remoteApi->getCoreMethods(new RemoteAPICoreTest());

        $remoteApi->call('wiki.voidTestMethod', array('something'));
    }

    function test_generalCoreFunctionWithArguments() {
        global $conf;
        global $USERINFO;
        $conf['remote'] = 1;
        $conf['remoteuser'] = '';
        $conf['useacl'] = 1;

        $remoteApi = new Api();
        $remoteApi->getCoreMethods(new RemoteAPICoreTest());

        $this->assertEquals($remoteApi->call('wiki.oneStringArgMethod', array('string')), 'string');
        $this->assertEquals($remoteApi->call('wiki.twoArgMethod', array('string', 1)), array('string' , 1));
        $this->assertEquals($remoteApi->call('wiki.twoArgWithDefaultArg', array('string')), array('string', 'default'));
        $this->assertEquals($remoteApi->call('wiki.twoArgWithDefaultArg', array('string', 'another')), array('string', 'another'));
    }

    function test_pluginCallMethods() {
        global $conf;
        global $USERINFO;
        $conf['remote'] = 1;
        $conf['remoteuser'] = '';
        $conf['useacl'] = 1;

        $remoteApi = new Api();
        $this->assertEquals($remoteApi->call('plugin.testing_manual.method1'), null);
        $this->assertEquals($remoteApi->call('plugin.testing_manual.method2', array('string', 7)), array('string', 7, false));
        $this->assertEquals($remoteApi->call('plugin.testing_manual.method2ext', array('string', 7, true)), array('string', 7, true));
        $this->assertEquals($remoteApi->call('plugin.testing_manual.methodString'), 'success');
    }

    /**
     * @expectedException dokuwiki\Remote\RemoteException
     */
    function test_notExistingCall() {
        global $conf;
        $conf['remote'] = 1;

        $remoteApi = new Api();
        $remoteApi->call('dose not exist');
    }

    function test_publicCallCore() {
        global $conf;
        $conf['useacl'] = 1;
        $remoteApi = new Api();
        $remoteApi->getCoreMethods(new RemoteAPICoreTest());
        $this->assertTrue($remoteApi->call('wiki.publicCall'));
    }

    function test_publicCallPlugin() {
        global $conf;
        $conf['useacl'] = 1;
        $remoteApi = new Api();
        $this->assertTrue($remoteApi->call('plugin.testing_manual.publicCall'));
    }

    /**
     * @expectedException dokuwiki\Remote\AccessDeniedException
     */
    function test_publicCallCoreDeny() {
        global $conf;
        $conf['useacl'] = 1;
        $remoteApi = new Api();
        $remoteApi->getCoreMethods(new RemoteAPICoreTest());
        $remoteApi->call('wiki.stringTestMethod');
    }

    /**
     * @expectedException dokuwiki\Remote\AccessDeniedException
     */
    function test_publicCallPluginDeny() {
        global $conf;
        $conf['useacl'] = 1;
        $remoteApi = new Api();
        $remoteApi->call('plugin.testing_manual.methodString');
    }

    function test_pluginCallCustomPath() {
        global $conf;
        global $USERINFO;
        $conf['remote'] = 1;
        $conf['remoteuser'] = '';
        $conf['useacl'] = 1;
        global $EVENT_HANDLER;
        $EVENT_HANDLER->register_hook('RPC_CALL_ADD', 'BEFORE', $this, 'pluginCallCustomPathRegister');

        $remoteApi = new Api();
        $result = $remoteApi->call('custom.path');
        $this->assertEquals($result, 'success');
    }

    function pluginCallCustomPathRegister(&$event, $param) {
        $event->data['custom.path'] = array('testing_manual', 'methodString');
    }
}
