<?php

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
        $conf['plugin']['authpdo']['dsn']  = 'sqlite:' . $this->dbfile;
        $conf['plugin']['authpdo']['user'] = '';
        $conf['plugin']['authpdo']['pass'] = '';


        $conf['plugin']['authpdo']['select-user'] = 'SELECT id as uid, login as user, name, pass as clear, mail FROM user WHERE login = :user';
        $conf['plugin']['authpdo']['select-user-groups'] = 'SELECT * FROM member AS m, "group" AS g  WHERE m.gid = g.id AND  m.uid = :uid';

    }

    public function tearDown() {
        parent::tearDown();
        unlink($this->dbfile);
    }

    public function test_userinfo() {
        global $conf;
        $auth = new auth_plugin_authpdo();

        // clear text pasword (with default config above
        $this->assertFalse($auth->checkPass('nobody', 'nope'));
        $this->assertFalse($auth->checkPass('admin', 'nope'));
        $this->assertTrue($auth->checkPass('admin', 'password'));

        // now with a hashed password
        $conf['plugin']['authpdo']['select-user'] = 'SELECT id as uid, login as user, name, pass as hash, mail FROM user WHERE login = :user';
        $this->assertFalse($auth->checkPass('admin', 'password'));
        $this->assertFalse($auth->checkPass('user', md5('password')));

        // access user data
        $info = $auth->getUserData('admin');
        $this->assertEquals('admin', $info['user']);
        $this->assertEquals('The Admin', $info['name']);
        $this->assertEquals('admin@example.com', $info['mail']);
        $this->assertEquals(array('admin','user'), $info['grps']);
    }
}
