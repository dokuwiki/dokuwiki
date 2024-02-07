<?php

namespace dokuwiki\plugin\usermanager\test;

use dokuwiki\Remote\AccessDeniedException;
use dokuwiki\Remote\Api;
use dokuwiki\Remote\RemoteException;
use DokuWikiTest;

/**
 * Remote API tests for the usermanager plugin
 *
 * @group plugin_usermanager
 * @group plugins
 */
class RemoteApiTest extends DokuWikiTest
{
    /** @var  Api */
    protected $remote;

    public function __construct()
    {
        parent::__construct();
        $this->remote = new Api();
    }

    public function setUp(): void
    {
        parent::setUp();

        global $conf;
        $conf['remote'] = 1;
        $conf['useacl'] = 1;
        $conf['remoteuser'] = 'umtestuser, admin';
        $conf['superuser'] = 'admin';
        $_SERVER['REMOTE_USER'] = '';
    }

    public function testCreateUserSuccess()
    {
        global $auth;
        $auth = new AuthPlugin();

        $params = [
            'user' => 'user1',
            'password' => 'password1',
            'name' => 'user one',
            'mail' => 'user1@localhost',
            'groups' => [
                'user',
                'test'
            ],
            'notify' => false
        ];

        $_SERVER['REMOTE_USER'] = 'admin';
        $this->assertTrue(
            $this->remote->call('plugin.usermanager.createUser', $params)
        );
        $this->assertArrayHasKey('user1', $auth->users);

        // try again should fail, because user already exists
        $this->assertFalse(
            $this->remote->call('plugin.usermanager.createUser', $params)
        );
    }

    public function testCreateUserFailAccess()
    {
        global $auth;
        $auth = new AuthPlugin();

        $params = [
            'user' => 'user1',
            'password' => 'password1',
            'name' => 'user one',
            'mail' => 'user1@localhost',
            'groups' => [
                'user',
                'test'
            ],
            'notify' => false
        ];

        $_SERVER['REMOTE_USER'] = 'umtestuser';

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionCode(114);
        $this->remote->call('plugin.usermanager.createUser', $params);
    }

    public function testCreateUserFailMissingUser()
    {
        global $auth;
        $auth = new AuthPlugin();

        $params = [
            'user' => '',
            'password' => 'password1',
            'name' => 'user one',
            'mail' => 'user1@localhost',
            'groups' => [
                'user',
                'test'
            ],
            'notify' => false
        ];

        $_SERVER['REMOTE_USER'] = 'admin';

        $this->expectException(RemoteException::class);
        $this->expectExceptionCode(401);
        $this->remote->call('plugin.usermanager.createUser', $params);
    }

    public function testCreateUserFailMissingName()
    {
        global $auth;
        $auth = new AuthPlugin();

        $params = [
            'user' => 'user1',
            'password' => 'password1',
            'name' => '',
            'mail' => 'user1@localhost',
            'groups' => [
                'user',
                'test'
            ],
            'notify' => false
        ];

        $_SERVER['REMOTE_USER'] = 'admin';

        $this->expectException(RemoteException::class);
        $this->expectExceptionCode(402);
        $this->remote->call('plugin.usermanager.createUser', $params);
    }

    public function testCreateUserFailBadEmail()
    {
        global $auth;
        $auth = new AuthPlugin();

        $params = [
            'user' => 'user1',
            'password' => 'password1',
            'name' => 'user one',
            'mail' => 'This is not an email',
            'groups' => [
                'user',
                'test'
            ],
            'notify' => false
        ];

        $_SERVER['REMOTE_USER'] = 'admin';

        $this->expectException(RemoteException::class);
        $this->expectExceptionCode(403);
        $this->remote->call('plugin.usermanager.createUser', $params);
    }

    public function testCreateUserFailAuthCapability()
    {
        global $auth;
        $auth = new AuthPlugin(['addUser' => false]);

        $params = [
            'user' => 'user1',
            'password' => 'password1',
            'name' => 'user one',
            'mail' => 'user1@localhost',
            'groups' => [
                'user',
                'test'
            ],
            'notify' => false
        ];

        $_SERVER['REMOTE_USER'] = 'admin';

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessageMatches('/can\'t do addUser/');
        $this->remote->call('plugin.usermanager.createUser', $params);
    }

    public function testDeleteUserSuccess()
    {
        global $auth;
        $auth = new AuthPlugin();
        $auth->users = [
            'user1' => [
                'pass' => 'password1',
                'name' => 'user one',
                'mail' => 'user1@localhost',
                'grps' => [
                    'user',
                    'test'
                ]
            ],
            'user2' => [
                'pass' => 'password2',
                'name' => 'user two',
                'mail' => 'user2@localhost',
                'grps' => [
                    'user',
                    'test'
                ]
            ],
        ];

        $_SERVER['REMOTE_USER'] = 'admin';

        $this->assertTrue($this->remote->call('plugin.usermanager.deleteUser', ['user' => 'user1']));
        $this->assertArrayNotHasKey('user1', $auth->users);
        $this->assertArrayHasKey('user2', $auth->users);
    }

    public function testDeleteUserFailAccess()
    {
        global $auth;
        $auth = new AuthPlugin();

        $_SERVER['REMOTE_USER'] = 'umtestuser';

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionCode(114);
        $this->remote->call('plugin.usermanager.deleteUser', ['user' => 'user1']);
    }


    public function testDeleteUserFailNoExist()
    {
        global $auth;
        $auth = new AuthPlugin();

        $_SERVER['REMOTE_USER'] = 'admin';

        $this->assertFalse($this->remote->call('plugin.usermanager.deleteUser', ['user' => 'user1']));
    }

    public function testDeleteUserFailAuthCapability()
    {
        global $auth;
        $auth = new AuthPlugin(['delUser' => false]);

        $_SERVER['REMOTE_USER'] = 'admin';

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessageMatches('/can\'t do delUser/');
        $this->remote->call('plugin.usermanager.deleteUser', ['user' => 'user1']);
    }
}
