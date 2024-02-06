<?php

namespace dokuwiki\test\Remote;

use dokuwiki\Remote\AccessDeniedException;
use dokuwiki\Remote\Api;
use dokuwiki\Remote\ApiCall;
use dokuwiki\Remote\RemoteException;
use dokuwiki\test\mock\AuthPlugin;
use dokuwiki\test\Remote\Mock\ApiCore;
use dokuwiki\test\Remote\Mock\TestPlugin1;
use dokuwiki\test\Remote\Mock\TestPlugin2;

class ApiTest extends \DokuWikiTest
{

    protected $userinfo;

    /** @var  Api */
    protected $remote;

    public function setUp(): void
    {
        parent::setUp();
        global $plugin_controller;
        global $conf;
        global $USERINFO;
        global $auth;

        parent::setUp();

        // mock plugin controller to return our test plugins
        $pluginManager = $this->createMock('dokuwiki\Extension\PluginController');
        $pluginManager->method('getList')->willReturn(array('testplugin', 'testplugin2'));
        $pluginManager->method('load')->willReturnCallback(
            function ($type, $plugin) {
                if ($plugin == 'testplugin2') {
                    return new TestPlugin2();
                } else {
                    return new TestPlugin1();
                }
            }
        );
        $plugin_controller = $pluginManager;

        $conf['remote'] = 1;
        $conf['remoteuser'] = '!!not set!!';
        $conf['useacl'] = 0;

        $this->userinfo = $USERINFO;
        $this->remote = new Api();

        $auth = new AuthPlugin();
    }

    public function tearDown(): void
    {
        global $USERINFO;
        $USERINFO = $this->userinfo;

    }

    public function testPluginMethods()
    {
        $methods = $this->remote->getPluginMethods();
        $actual = array_keys($methods);
        sort($actual);
        $expect = array(
            'plugin.testplugin.method1',
            'plugin.testplugin.method2',
            'plugin.testplugin.methodString',
            'plugin.testplugin.method2ext',
            'plugin.testplugin.publicCall',

            'plugin.testplugin2.commented'
        );
        sort($expect);
        $this->assertEquals($expect, $actual);
    }

    public function testPluginDescriptors()
    {
        $methods = $this->remote->getPluginMethods();
        $this->assertEquals(
            [
                'str' => [
                    'type' => 'string',
                    'description' => 'some more parameter description',
                    'optional' => false,
                ],
                'int' => [
                    'type' => 'int',
                    'description' => '',
                    'optional' => false,
                ],
                'bool' => [
                    'type' => 'bool',
                    'description' => '',
                    'optional' => false,
                ],
                'array' => [
                    'type' => 'array',
                    'description' => '',
                    'optional' => true,
                    'default' => [],
                ],
            ],
            $methods['plugin.testplugin2.commented']->getArgs()
        );

        $this->assertEquals(
            [
                'type' => 'array',
                'description' => '',
            ],
            $methods['plugin.testplugin2.commented']->getReturn()
        );

        $this->assertEquals(0, $methods['plugin.testplugin2.commented']->isPublic());
        $this->assertStringContainsString(
            'This is a dummy method',
            $methods['plugin.testplugin2.commented']->getSummary()
        );
    }

    public function testHasAccessSuccess()
    {
        global $conf;
        $conf['remoteuser'] = '';

        $this->remote->ensureAccessIsAllowed(new ApiCall('time'));
        $this->assertTrue(true);
    }

    public function testHasAccessFailAcl()
    {
        global $conf;
        $conf['useacl'] = 1;

        $this->expectException(AccessDeniedException::class);
        $this->remote->ensureAccessIsAllowed(new ApiCall('time'));
    }

    public function testHasAccessSuccessAclEmptyRemoteUser()
    {
        global $conf;
        $conf['useacl'] = 1;
        $conf['remoteuser'] = '';

        $this->remote->ensureAccessIsAllowed(new ApiCall('time'));
        $this->assertTrue(true);
    }

    public function testHasAccessSuccessAcl()
    {
        global $conf;
        global $USERINFO;
        $conf['useacl'] = 1;
        $conf['remoteuser'] = '@grp,@grp2';
        $USERINFO['grps'] = array('grp');

        $this->remote->ensureAccessIsAllowed(new ApiCall('time'));
        $this->assertTrue(true);
    }

    public function testHasAccessFailAcl2()
    {
        global $conf;
        global $USERINFO;
        $conf['useacl'] = 1;
        $conf['remoteuser'] = '@grp';
        $USERINFO['grps'] = array('grp1');

        $this->expectException(AccessDeniedException::class);
        $this->remote->ensureAccessIsAllowed(new ApiCall('time'));
    }

    public function testIsEnabledFail1()
    {
        global $conf;
        $conf['remote'] = 0;
        $this->expectException(AccessDeniedException::class);
        $this->remote->ensureApiIsEnabled();
    }

    public function testIsEnabledFail2()
    {
        global $conf;
        $conf['remoteuser'] = '!!not set!!';
        $this->expectException(AccessDeniedException::class);
        $this->remote->ensureApiIsEnabled();
    }

    public function testIsEnabledSuccess()
    {
        global $conf;
        $conf['remote'] = 1;
        $conf['remoteuser'] = '';
        $this->remote->ensureApiIsEnabled();
        $this->assertTrue(true);
    }


    public function testGeneralCoreFunctionWithoutArguments()
    {
        global $conf;
        global $USERINFO;
        $conf['remote'] = 1;
        $conf['remoteuser'] = '';
        $conf['useacl'] = 1;
        $USERINFO['grps'] = array('grp');
        $remoteApi = new Api();
        $remoteApi->getCoreMethods(new ApiCore());

        $this->assertEquals($remoteApi->call('wiki.stringTestMethod'), 'success');
        $this->assertEquals($remoteApi->call('wiki.intTestMethod'), 42);
        $this->assertEquals($remoteApi->call('wiki.floatTestMethod'), 3.14159265);
        $this->assertEquals($remoteApi->call('wiki.dateTestMethod'), 2623452346);
        $this->assertEquals($remoteApi->call('wiki.fileTestMethod'), 'file content');
        $this->assertEquals($remoteApi->call('wiki.voidTestMethod'), null);
    }

    public function testGeneralCoreFunctionOnArgumentMismatch()
    {
        global $conf;
        $conf['remote'] = 1;
        $remoteApi = new Api();
        $remoteApi->getCoreMethods(new ApiCore());

        try {
            $remoteApi->call('wiki.voidTestMethod', array('something'));
            $this->fail('Expects RemoteException to be raised');
        } catch (RemoteException $th) {
            $this->assertEquals(-32604, $th->getCode());
        }
    }

    public function testGeneralCoreFunctionWithArguments()
    {
        global $conf;
        global $USERINFO;
        $conf['remote'] = 1;
        $conf['remoteuser'] = '';
        $conf['useacl'] = 1;

        $remoteApi = new Api();
        $remoteApi->getCoreMethods(new ApiCore());

        $this->assertEquals($remoteApi->call('wiki.oneStringArgMethod', array('string')), 'string');
        $this->assertEquals($remoteApi->call('wiki.twoArgMethod', array('string', 1)), array('string', 1));
        $this->assertEquals($remoteApi->call('wiki.twoArgWithDefaultArg', array('string')), array('string', 'default'));
        $this->assertEquals($remoteApi->call('wiki.twoArgWithDefaultArg', array('string', 'another')), array('string', 'another'));
    }

    public function testGeneralCoreFunctionOnArgumentMissing()
    {
        global $conf;
        $conf['remote'] = 1;
        $conf['remoteuser'] = '';
        $remoteApi = new Api();
        $remoteApi->getCoreMethods(new ApiCore());

        $this->expectException(RemoteException::class);
        $this->expectExceptionCode(-32602);

        $remoteApi->call('wiki.twoArgWithDefaultArg', array());
    }

    public function testPluginCallMethods()
    {
        global $conf;
        global $USERINFO;
        $conf['remote'] = 1;
        $conf['remoteuser'] = '';
        $conf['useacl'] = 1;

        $remoteApi = new Api();
        $this->assertEquals($remoteApi->call('plugin.testplugin.method1'), null);
        $this->assertEquals($remoteApi->call('plugin.testplugin.method2', array('string', 7)), array('string', 7, false));
        $this->assertEquals($remoteApi->call('plugin.testplugin.method2ext', array('string', 7, true)), array('string', 7, true));
        $this->assertEquals($remoteApi->call('plugin.testplugin.methodString'), 'success');
    }

    public function testPluginCallMethodsOnArgumentMissing()
    {
        global $conf;
        $conf['remote'] = 1;
        $conf['remoteuser'] = '';
        $remoteApi = new Api();
        $remoteApi->getCoreMethods(new ApiCore());

        $this->expectException(RemoteException::class);
        $this->expectExceptionCode(-32602);
        $remoteApi->call('plugin.testplugin.method2', array());
    }

    public function testNotExistingCall()
    {
        global $conf;
        $conf['remote'] = 1;
        $conf['remoteuser'] = '';

        $this->expectException(RemoteException::class);
        $this->expectExceptionCode(-32603);

        $remoteApi = new Api();
        $remoteApi->call('does.not exist'); // unknown method type
    }

    public function testPublicCallCore()
    {
        global $conf;
        $conf['useacl'] = 1;
        $conf['remote'] = 1;
        $conf['remoteuser'] = 'restricted';
        $remoteApi = new Api();
        $remoteApi->getCoreMethods(new ApiCore());
        $this->assertTrue($remoteApi->call('wiki.publicCall'));
    }

    public function testPublicCallPlugin()
    {
        global $conf;
        $conf['useacl'] = 1;
        $conf['remote'] = 1;
        $conf['remoteuser'] = 'restricted';
        $remoteApi = new Api();
        $this->assertTrue($remoteApi->call('plugin.testplugin.publicCall'));
    }

    public function testPublicCallCoreDeny()
    {
        global $conf;
        $conf['useacl'] = 1;
        $this->expectException(AccessDeniedException::class);
        $remoteApi = new Api();
        $remoteApi->getCoreMethods(new ApiCore());
        $remoteApi->call('wiki.stringTestMethod');
    }

    public function testPublicCallPluginDeny()
    {
        global $conf;
        $conf['useacl'] = 1;
        $this->expectException(AccessDeniedException::class);
        $remoteApi = new Api();
        $remoteApi->call('plugin.testplugin.methodString');
    }
}
