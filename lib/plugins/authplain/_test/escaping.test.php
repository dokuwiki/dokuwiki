<?php

/**
 * These tests are designed to test the capacity of pluginauth to handle
 * correct escaping of colon field delimiters and backslashes in user content.
 *
 * (Note that these tests set some Real Names, etc. that are may not be
 * valid in the broader dokuwiki context, but the tests ensure that
 * authplain won't get unexpectedly surprised.)
 *
 * @group plugin_authplain
 * @group auth_plugins
 * @group plugins
 * @group bundled_plugins
 */
class helper_plugin_authplain_escaping_test extends DokuWikiTest {

    protected $pluginsEnabled = array('authplain');
    /** @var  auth_plugin_authplain */
    protected $auth;

    protected function reloadUsers() {
        /* auth caches data loaded from file, but recreated object forces reload */
        $this->auth = new auth_plugin_authplain();
    }

    function setUp() : void {
        global $config_cascade;
        parent::setUp();
        $name = $config_cascade['plainauth.users']['default'];
        copy($name, $name.".orig");
        $this->reloadUsers();
    }

    function tearDown() : void {
        global $config_cascade;
        parent::tearDown();
        $name = $config_cascade['plainauth.users']['default'];
        copy($name.".orig", $name);
    }

    public function testMediawikiPasswordHash() {
        global $conf;
        $conf['passcrypt'] = 'mediawiki';
        $this->auth->createUser("mwuser", "12345", "Mediawiki User", "me@example.com");
        $this->reloadUsers();
        $this->assertTrue($this->auth->checkPass("mwuser", "12345"));
        $mwuser = $this->auth->getUserData("mwuser");
        $this->assertStringStartsWith(":B:",$mwuser['pass']);
        $this->assertEquals("Mediawiki User",$mwuser['name']);
    }

    public function testNameWithColons() {
        $name = ":Colon: User:";
        $this->auth->createUser("colonuser", "password", $name, "me@example.com");
        $this->reloadUsers();
        $user = $this->auth->getUserData("colonuser");
        $this->assertEquals($name,$user['name']);
    }

    public function testNameWithBackslashes() {
        $name = "\\Slash\\ User\\";
        $this->auth->createUser("slashuser", "password", $name, "me@example.com");
        $this->reloadUsers();
        $user = $this->auth->getUserData("slashuser");
        $this->assertEquals($name,$user['name']);
    }

    public function testNameWithHash() {
        $name = "Hash # User";
        $this->auth->createUser("slashuser", "password", $name, "me@example.com");
        $this->reloadUsers();
        $user = $this->auth->getUserData("slashuser");
        $this->assertEquals($name,$user['name']);
    }

    public function testModifyUser() {
        global $conf;
        $conf['passcrypt'] = 'mediawiki';
        $user = $this->auth->getUserData("testuser");
        $user['name'] = "\\New:Crazy:Name\\";
        $user['pass'] = "awesome new password";
        $this->auth->modifyUser("testuser", $user);
        $this->reloadUsers();

        $saved = $this->auth->getUserData("testuser");
        $this->assertEquals($saved['name'], $user['name']);
        $this->assertTrue($this->auth->checkPass("testuser", $user['pass']));
    }

    /**
     * @see testModifyUserRegexMetacharacter
     */
    public function provideRegexMetacharacterUsers()
    {
        // Only the dot actually survives cleanUser() and is thus reachable as
        // a stored username through the normal user flow. The other
        // metacharacters are normally stripped during cleaning and cannot
        // occur that way, but are exercised here directly to ensure
        // modifyUser() escapes the username robustly on its own.
        // [attacker username, sibling that an unescaped /^<attacker>:/ matches]
        return [
            ['a.b', 'a1b'],   // . matches any single character (reachable via cleanUser)
            ['xy+', 'xyy'],   // + matches the repeated preceding character
            ['ab?', 'a'],     // ? makes the preceding character optional
            ['c[d', 'c[d'],   // [ would make the unescaped pattern an invalid regex
        ];
    }

    /**
     * Modifying a user whose name contains a regex metacharacter must only
     * touch that user's own line and never match or destroy other accounts.
     *
     * @param string $attacker username containing a regex metacharacter
     * @param string $sibling  account an unescaped pattern would also match
     * @dataProvider provideRegexMetacharacterUsers
     */
    public function testModifyUserRegexMetacharacter($attacker, $sibling)
    {
        $this->auth->createUser($attacker, "password", "Attacker", "a@example.com");
        if ($sibling !== $attacker) {
            $this->auth->createUser($sibling, "password", "Sibling", "s@example.com");
        }
        $this->reloadUsers();

        // the attacker updates only their own profile
        $user = $this->auth->getUserData($attacker);
        $user['name'] = "Renamed";
        $this->assertTrue($this->auth->modifyUser($attacker, $user));
        $this->reloadUsers();

        // the attacker's own change took effect
        $this->assertEquals("Renamed", $this->auth->getUserData($attacker)['name']);

        // the sibling account must survive untouched
        $saved = $this->auth->getUserData($sibling);
        $this->assertNotFalse($saved, "sibling account '$sibling' was destroyed");
        if ($sibling !== $attacker) {
            $this->assertEquals("Sibling", $saved['name']);
        }
    }

    // really only required for developers to ensure this plugin will
    // work with systems running on PCRE 6.6 and lower.
    public function testLineSplit(){
        $names = array(
          'plain',
          'ut-fठ8',
          'colon:',
          'backslash\\',
          'alltogether\\ठ:'
        );
        $userpass = 'user:password_hash:';
        $other_user_data = ':email@address:group1,group2';

        foreach ($names as $testname) {
            $escaped = str_replace(array('\\',':'),array('\\\\','\\:'),$testname);   // escape : & \
            $test_line = $userpass.$escaped.$other_user_data;
            $result = $this->callInaccessibleMethod($this->auth, 'splitUserData', [$test_line]);

            $this->assertEquals($escaped, $result[2]);
        }
    }

    /**
     * @see testCleaning
     */
    public function provideCleaning()
    {
        return [
            ['user', 'user'],
            ['USER', 'user'],
            [' USER ', 'user'],
            [' US ER ', 'us_er'],
            ['http://foo;bar', 'http_foo_bar'],
        ];
    }

    /**
     * @param string $input
     * @param string $expected
     * @dataProvider provideCleaning
     */
    public function testCleaning($input, $expected)
    {
        $this->assertEquals($expected, $this->auth->cleanUser($input));
        $this->assertEquals($expected, $this->auth->cleanGroup($input));
    }
}
