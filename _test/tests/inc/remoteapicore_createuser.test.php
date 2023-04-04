<?php

use dokuwiki\Remote\AccessDeniedException;
use dokuwiki\Remote\Api;
use dokuwiki\Remote\RemoteException;
use dokuwiki\test\mock\AuthPlugin;
use dokuwiki\test\mock\AuthCreatePlugin;

/**
 * Class remoteapicore_test
 */
class remoteapicore_createuser_test extends DokuWikiTest {

    protected $userinfo;
    protected $oldAuthAcl;
    /** @var  Api */
    protected $remote;

    public function setUp() : void {
        // we need a clean setup before each single test:
        DokuWikiTest::setUpBeforeClass();

        parent::setUp();
        global $conf;
        global $USERINFO;
        global $AUTH_ACL;
        global $auth;
        $this->oldAuthAcl = $AUTH_ACL;
        $this->userinfo = $USERINFO;
        $auth = new AuthPlugin();

        $conf['remote'] = 1;
        $conf['remoteuser'] = '@user';
        $conf['useacl'] = 0;

        $this->remote = new Api();
    }

    public function tearDown() : void {
        parent::tearDown();

        global $USERINFO;
        global $AUTH_ACL;

        $USERINFO = $this->userinfo;
        $AUTH_ACL = $this->oldAuthAcl;
    }

    public function test_createUser()
    {
        global $conf, $auth;
        $conf['remote'] = 1;
        $conf['remoteuser'] = 'testuser';
        $_SERVER['REMOTE_USER'] = 'testuser';

        $auth = new AuthCreatePlugin();
        // $user, $pwd, $name, $mail, $grps = null
        $params = [
            [
                'user' => 'user1',
                'password' => 'password1',
                'name' => 'user1',
                'mail' => 'user1@localhost',
                'groups' => [
                    'user',
                    'test'
                ],
                'notify' => false
            ]
        ];

        $actualCallResult = $this->remote->call('dokuwiki.createUser', $params);
        $this->assertTrue($actualCallResult);

        // if the user exists, no data is overwritten
        $actualCallResult = $this->remote->call('dokuwiki.createUser', $params);
        $this->assertFalse($actualCallResult);
    }

    public function test_createUserAuthPlain()
    {
        global $conf, $auth;
        $conf['remote'] = 1;
        $conf['remoteuser'] = 'testuser';
        $_SERVER['REMOTE_USER'] = 'testuser';
        $auth = new auth_plugin_authplain();
        $params = [
                [
                    'user' => 'user1',
                    'password' => 'password1',
                    'name' => 'user1',
                    'mail' => 'user1@localhost',
                    'groups' => [
                        'user',
                        'test'
                    ],
                    'notify' => false
                ]

        ];

        $callResult = $this->remote->call('dokuwiki.createUser', $params);
        $this->assertTrue($callResult);
    }

    public function test_createUserAuthPlainUndefinedUser()
    {
        global $conf, $auth;
        $conf['remote'] = 1;
        $conf['remoteuser'] = 'testuser';
        $_SERVER['REMOTE_USER'] = 'testuser';
        $auth = new auth_plugin_authplain();
        $params = [
                [
                    'user' => ''
                ],
        ];

        $this->expectException(RemoteException::class);
        $this->expectExceptionCode(401);
        $this->remote->call('dokuwiki.createUser', $params);
    }

    public function test_createUserAuthPlainUndefinedName()
    {
        global $conf, $auth;
        $conf['remote'] = 1;
        $conf['remoteuser'] = 'testuser';
        $_SERVER['REMOTE_USER'] = 'testuser';
        $auth = new auth_plugin_authplain();
        $params = [
            [
                'user' => 'hello'
            ],
        ];

        $this->expectException(RemoteException::class);
        $this->expectExceptionCode(402);
        $this->remote->call('dokuwiki.createUser', $params);
    }

    public function test_createUserAuthPlainBadEmail()
    {
        global $conf, $auth;
        $conf['remote'] = 1;
        $conf['remoteuser'] = 'testuser';
        $_SERVER['REMOTE_USER'] = 'testuser';
        $auth = new auth_plugin_authplain();
        $params = [
            [
                'user' => 'hello',
                'name' => 'A new user',
                'mail' => 'this is not an email address'
            ],
        ];

        $this->expectException(RemoteException::class);
        $this->expectExceptionCode(403);
        $this->remote->call('dokuwiki.createUser', $params);
    }

    public function test_createUserAuthCanNotDoAddUser()
    {
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessageMatches('/can\'t do addUser/');
        global $conf, $auth;
        $conf['remote'] = 1;
        $conf['remoteuser'] = 'testuser';
        $_SERVER['REMOTE_USER'] = 'testuser';

        $auth = new AuthCreatePlugin(false);
        $params = [
                [
                    'user' => 'user1',
                    'password' => 'password1',
                    'name' => 'user1',
                    'mail' => 'user1@localhost',
                    'groups' => [
                        'user',
                        'test'
                    ],
                    'notify' => false
                ],
        ];
        $this->remote->call('dokuwiki.createUser', $params);
    }

}
