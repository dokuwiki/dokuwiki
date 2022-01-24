<?php

use dokuwiki\Remote\AccessDeniedException;
use dokuwiki\Remote\Api;
use dokuwiki\Remote\RemoteException;
use dokuwiki\test\mock\AuthPlugin;
use dokuwiki\test\mock\AuthCreatePlugin;

/**
 * Class remoteapicore_test
 */
class remoteapicore_createusers_test extends DokuWikiTest {

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

    /** Delay writes of old revisions by a second. */
    public function handle_write(Doku_Event $event, $param) {
        if ($event->data[3] !== false) {
            $this->waitForTick();
        }
    }


    public function test_createUsers()
    {
        global $conf, $auth;
        $conf['remote'] = 1;
        $conf['remoteuser'] = 'testuser';
        $_SERVER['REMOTE_USER'] = 'testuser';

        $auth = new AuthCreatePlugin();
        // $user, $pwd, $name, $mail, $grps = null
        $params = [
            [
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
                [
                    'user' => 'user2',
                    'password' => 'password2',
                    'name' => 'user2',
                    'mail' => 'user2@localhost',
                    'groups' => [
                        'user',
                        'test'
                    ],
                    'notify' => true
                ],
            ]
        ];

        $actualCallResult = $this->remote->call('dokuwiki.createUsers', $params);
        $this->assertCount(2, $actualCallResult);
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
            ]
        ];

        $callResult = $this->remote->call('dokuwiki.createUsers', $params);
        $this->assertEquals('user1', $callResult[0]);
    }

    public function test_createUserAuthPlainUndefinedParams()
    {
        $this->expectException(RemoteException::class);
        $this->expectExceptionMessageMatches('/invalid data/');
        global $conf, $auth;
        $conf['remote'] = 1;
        $conf['remoteuser'] = 'testuser';
        $_SERVER['REMOTE_USER'] = 'testuser';
        $auth = new auth_plugin_authplain();
        $params = [
            [
                [
                    'user' => ''
                ],
            ]
        ];

        $this->remote->call('dokuwiki.createUsers', $params);

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
            ]
        ];
        $this->remote->call('dokuwiki.createUsers', $params);
    }

}
