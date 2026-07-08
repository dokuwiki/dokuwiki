<?php

namespace dokuwiki\test\Remote;

use dokuwiki\Remote\AccessDeniedException;
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

    /**
     * A regular (non-admin) user may check their own permissions.
     */
    public function testCheckaclSelf() {
        global $conf;
        global $AUTH_ACL, $USERINFO;

        $conf['useacl'] = 1;
        $_SERVER['REMOTE_USER'] = 'john';
        $USERINFO['grps'] = ['user'];
        $AUTH_ACL = [
            '*                  @ALL           0', //none
            '*                  @user          2', //edit
        ];

        // no user given -> current user is used
        $this->assertEquals(AUTH_EDIT, $this->remote->call('core.aclCheck', ['nice_page']));

        // naming yourself explicitly is allowed too
        $params = [
            'nice_page',
            'john',
            ['user']
        ];
        $this->assertEquals(AUTH_EDIT, $this->remote->call('core.aclCheck', $params));
    }

    /**
     * On a case-insensitive backend a user may check their own permissions even
     * when naming themselves in a different case than their logged-in name.
     */
    public function testCheckaclSelfCaseInsensitiveBackend() {
        global $conf;
        global $AUTH_ACL, $USERINFO;
        global $auth;

        // logging in as "john" and naming "John" refer to the same user here
        $auth = new class extends \auth_plugin_authplain {
            public function isCaseSensitive() {
                return false;
            }
        };

        $conf['useacl'] = 1;
        $_SERVER['REMOTE_USER'] = 'john';
        $USERINFO['grps'] = ['user'];
        $AUTH_ACL = [
            '*                  @ALL           0', //none
            '*                  @user          2', //edit
        ];

        // naming yourself in a different case must not be treated as another user
        $params = [
            'nice_page',
            'John',
            ['user']
        ];
        $this->assertEquals(AUTH_EDIT, $this->remote->call('core.aclCheck', $params));
    }

    /**
     * Checking another user's permissions is restricted to superusers and must
     * be denied for a regular user.
     */
    public function testCheckaclOtherUserDeniedForNonAdmin() {
        global $conf;
        global $AUTH_ACL, $USERINFO;

        $conf['useacl'] = 1;
        $_SERVER['REMOTE_USER'] = 'john';
        $USERINFO['grps'] = ['user'];
        $AUTH_ACL = [
            '*                  @ALL           0', //none
            '*                  @user          2', //edit
        ];

        $this->expectException(AccessDeniedException::class);
        $this->remote->call('core.aclCheck', ['nice_page', 'someoneelse', ['user']]);
    }

    /**
     * A superuser may check the permissions of arbitrary users and groups.
     */
    public function testCheckaclOtherUsersAsAdmin() {
        global $conf;
        global $AUTH_ACL, $USERINFO;
        /** @var auth_plugin_authplain $auth */
        global $auth;

        $conf['useacl'] = 1;
        $_SERVER['REMOTE_USER'] = 'testuser'; // configured superuser, see _test/conf/local.php
        $USERINFO['grps'] = ['user'];
        $AUTH_ACL = [
            '*                  @ALL           0', //none
            '*                  @user          2', //edit
            '*                  @more          4', //create
            'nice_page          user2          8'  //upload
        ];

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
