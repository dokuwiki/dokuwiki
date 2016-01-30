<?php


class testable_auth_plugin_authpdo extends auth_plugin_authpdo {
    public function getPluginName() {
        return 'authpdo';
    }

    public function _selectGroups() {
        return parent::_selectGroups();
    }

    public function _insertGroup($group) {
        return parent::_insertGroup($group);
    }

}

/**
 * General tests for the authpdo plugin
 *
 * @group plugin_authpdo
 * @group plugins
 */
class sqlite_plugin_authpdo_test extends DokuWikiTest {

    protected $dbfile;

    public function setUp() {
        parent::setUp();
        $this->dbfile = tempnam('/tmp/', 'pluginpdo_test_');
        copy(__DIR__ . '/test.sqlite3', $this->dbfile);

        global $conf;

        $conf['plugin']['authpdo']['debug'] = 1;
        $conf['plugin']['authpdo']['dsn'] = 'sqlite:' . $this->dbfile;
        $conf['plugin']['authpdo']['user'] = '';
        $conf['plugin']['authpdo']['pass'] = '';

        $conf['plugin']['authpdo']['select-user'] = 'SELECT id AS uid, login AS user, name, pass AS clear, mail FROM user WHERE login = :user';
        $conf['plugin']['authpdo']['select-user-groups'] = 'SELECT * FROM member AS m, "group" AS g  WHERE m.gid = g.id AND  m.uid = :uid';
        $conf['plugin']['authpdo']['select-groups'] = 'SELECT id AS gid, "group" FROM "group"';
        $conf['plugin']['authpdo']['insert-user'] = 'INSERT INTO user (login, pass, name, mail) VALUES (:user, :hash, :name, :mail)';
        $conf['plugin']['authpdo']['insert-group'] = 'INSERT INTO "group" ("group") VALUES (:group)';
        $conf['plugin']['authpdo']['join-group'] = 'INSERT INTO member (uid, gid) VALUES (:uid, :gid)';
    }

    public function tearDown() {
        parent::tearDown();
        unlink($this->dbfile);
    }

    public function test_internals() {
        $auth = new testable_auth_plugin_authpdo();

        $groups = $auth->_selectGroups();
        $this->assertArrayHasKey('user', $groups);
        $this->assertEquals(1, $groups['user']['gid']);
        $this->assertArrayHasKey('admin', $groups);
        $this->assertEquals(2, $groups['admin']['gid']);

        $ok = $auth->_insertGroup('test');
        $this->assertTrue($ok);
        $groups = $auth->_selectGroups();
        $this->assertArrayHasKey('test', $groups);
        $this->assertEquals(3, $groups['test']['gid']);
    }

    public function test_userinfo() {
        global $conf;
        $auth = new auth_plugin_authpdo();

        // clear text pasword (with default config above
        $this->assertFalse($auth->checkPass('nobody', 'nope'));
        $this->assertFalse($auth->checkPass('admin', 'nope'));
        $this->assertTrue($auth->checkPass('admin', 'password'));

        // now with a hashed password
        $conf['plugin']['authpdo']['select-user'] = 'SELECT id AS uid, login AS user, name, pass AS hash, mail FROM user WHERE login = :user';
        $this->assertFalse($auth->checkPass('admin', 'password'));
        $this->assertFalse($auth->checkPass('user', md5('password')));

        // access user data
        $info = $auth->getUserData('admin');
        $this->assertEquals('admin', $info['user']);
        $this->assertEquals('The Admin', $info['name']);
        $this->assertEquals('admin@example.com', $info['mail']);
        $this->assertEquals(array('admin', 'user'), $info['grps']);

        // group retrieval
        $this->assertEquals(array('admin', 'user'), $auth->retrieveGroups());
        $this->assertEquals(array('user'), $auth->retrieveGroups(1));
        $this->assertEquals(array('admin'), $auth->retrieveGroups(0, 1));

        // user creation
        $auth->createUser('test', 'password', 'A Test user', 'test@example.com', array('newgroup'));
        $info = $auth->getUserData('test');
        $this->assertEquals('test', $info['user']);
        $this->assertEquals('A Test user', $info['name']);
        $this->assertEquals('test@example.com', $info['mail']);
        $this->assertEquals(array('newgroup', 'user'), $info['grps']);
        $this->assertEquals(array('admin', 'newgroup', 'user'), $auth->retrieveGroups());
    }

}
