<?php

namespace dokuwiki\test\Remote;

use dokuwiki\Remote\Api;

/**
 * Class remoteapicore_test
 */
class ApiCoreAclCheckTest extends \DokuWikiTest {

    protected $userinfo;
    protected $oldAuthAcl;
    /** @var  Api */
    protected $remote;

    protected $pluginsEnabled = array('auth_plugin_authplain');

    protected function reloadUsers() {
        global $auth;

        /* auth caches data loaded from file, but recreated object forces reload */
        $auth = new \auth_plugin_authplain();
    }

    public function setUp() : void {
        global $config_cascade;
        global $conf;
        global $USERINFO;
        global $AUTH_ACL;

        parent::setUp();

        $name = $config_cascade['plainauth.users']['default'];
        copy($name, $name . ".orig");
        $this->reloadUsers();

        $this->oldAuthAcl = $AUTH_ACL;
        $this->userinfo = $USERINFO;

        $conf['remote'] = 1;
        $conf['remoteuser'] = '@user';
        $conf['useacl'] = 0;

        $this->remote = new Api();

    }

    public function tearDown() : void {
        global $USERINFO;
        global $AUTH_ACL;
        global $config_cascade;

        parent::tearDown();

        $USERINFO = $this->userinfo;
        $AUTH_ACL = $this->oldAuthAcl;

        $name = $config_cascade['plainauth.users']['default'];
        copy($name . ".orig", $name);
    }

    public function testCheckacl() {
        global $conf;
        global $AUTH_ACL, $USERINFO;
        /** @var auth_plugin_authplain $auth */
        global $auth;

        $conf['useacl'] = 1;
        $_SERVER['REMOTE_USER'] = 'john';
        $USERINFO['grps'] = ['user'];
        $AUTH_ACL = [
            '*                  @ALL           0', //none
            '*                  @user          2', //edit
            '*                  @more          4', //create
            'nice_page          user2          8'  //upload
        ];

        $params = ['nice_page'];
        $this->assertEquals(AUTH_EDIT, $this->remote->call('core.aclCheck', $params));

        $auth->createUser("user1", "54321", "a User", "you@example.com");
        $auth->createUser("user2", "543210", "You", "he@example.com");
        $auth->createUser("mwuser", "12345", "Wiki User", "me@example.com", ['more']); //not in default group

        $params = [
            'nice_page',
            'user1'
        ];
        $this->assertEquals(AUTH_EDIT, $this->remote->call('core.aclCheck', $params));

        $params = [
            'nice_page',
            'mwuser',
            // member of group 'more' (automatically retrieved)
        ];
        $this->assertEquals(AUTH_CREATE, $this->remote->call('core.aclCheck', $params));

        $params = [
            'nice_page',
            'mwuser',
            [] // member of group 'more' (automatically retrieved)
        ];
        $this->assertEquals(AUTH_CREATE, $this->remote->call('core.aclCheck', $params));

        $params = [
            'nice_page',
            'notexistinguser',
            ['more']
        ];
        $this->assertEquals(AUTH_CREATE, $this->remote->call('core.aclCheck', $params));

        $params = [
            'nice_page',
            'user2',
            // (automatically retrieved)
        ];
        $this->assertEquals(AUTH_UPLOAD, $this->remote->call('core.aclCheck', $params));

        $params = [
            'nice_page',
            'user2',
            [] // (automatically retrieved)
        ];
        $this->assertEquals(AUTH_UPLOAD, $this->remote->call('core.aclCheck', $params));

        $params = [
            'unknown_page',
            'user2',
            // (automatically retrieved)
        ];
        $this->assertEquals(AUTH_EDIT, $this->remote->call('core.aclCheck', $params));

        $params = [
            'unknown_page',
            'user2',
            [] // (automatically retrieved)
        ];
        $this->assertEquals(AUTH_EDIT, $this->remote->call('core.aclCheck', $params));

        $params = array(
            'nice_page',
            'testuser', // superuser set via conf
            // (automatically retrieved)
        );
        $this->assertEquals(AUTH_ADMIN, $this->remote->call('core.aclCheck', $params));
    }

}
