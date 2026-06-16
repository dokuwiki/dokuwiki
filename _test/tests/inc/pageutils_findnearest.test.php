<?php

use dokuwiki\test\mock\AuthPlugin;

class pageutils_findnearest_test extends DokuWikiTest {

    protected $oldAuthAcl;

    function setUp() : void {
        parent::setUp();
        global $AUTH_ACL;
        global $auth;
        global $conf;
        $conf['superuser'] = 'john';
        $conf['useacl']    = 1;

        $this->oldAuthAcl = $AUTH_ACL;
        $auth = new AuthPlugin();

        $AUTH_ACL = array(
            '*           @ALL           1',
            'internal:*    @ALL           0',
            'internal:*    max            1',
            '*           @user          8',
        );
    }

    function tearDown() : void {
        global $AUTH_ACL;
        $AUTH_ACL = $this->oldAuthAcl;
    }

    function testNoSidebar() {
        global $ID;

        $ID = 'foo:bar:baz:test';
        $sidebar = page_findnearest('sidebar');
        $this->assertEquals(false, $sidebar);
    }

    function testZeroID() {
        global $ID;

        saveWikiText('sidebar', 'topsidebar-test', '');
        saveWikiText('0', 'zero-test', '');
        saveWikiText('0:0:0', 'zero-test', '');

        $ID = '0:0:0';
        $sidebar = page_findnearest('sidebar');
        $this->assertEquals('sidebar', $sidebar);

        $sidebar = page_findnearest('0');
        $this->assertEquals('0:0:0', $sidebar);

        $ID = '0';
        $sidebar = page_findnearest('0');
        $this->assertEquals('0', $sidebar);
    }

    function testLikeNSSidebar() {
        global $ID;

        saveWikiText('foo:bar2:start', 'startPage', '');
        saveWikiText('foo:bar2:sidebar', 'sidebarInside', '');
        saveWikiText('foo:bar2:deeper:sidebar', 'sidebarInside2', '');

        $ID = 'foo:bar2';
        $sidebar = page_findnearest('sidebar');
        $this->assertEquals('sidebar', $sidebar);

        $ID = 'foo:bar2:start';
        $sidebar = page_findnearest('sidebar');
        $this->assertEquals('foo:bar2:sidebar', $sidebar);

        $ID = 'foo:bar2:newpage';
        $sidebar = page_findnearest('sidebar');
        $this->assertEquals('foo:bar2:sidebar', $sidebar);

        $ID = 'foo:bar2:deeper';
        $sidebar = page_findnearest('sidebar');
        $this->assertEquals('foo:bar2:deeper:sidebar', $sidebar);

        $ID = 'foo:bar2:deeper:page';
        $sidebar = page_findnearest('sidebar');
        $this->assertEquals('foo:bar2:deeper:sidebar', $sidebar);

        saveWikiText('foo:bar3', 'startPage', '');
        saveWikiText('foo:bar3:start', 'innerStartPage', '');
        saveWikiText('foo:bar3:sidebar', 'sidebarInside', '');

        $ID = 'foo:bar3';
        $sidebar = page_findnearest('sidebar');
        $this->assertEquals('sidebar', $sidebar);

        $ID = 'foo:bar3:test';
        $sidebar = page_findnearest('sidebar');
        $this->assertEquals('foo:bar3:sidebar', $sidebar);

        saveWikiText('foo:bar4', 'startPage', '');
        saveWikiText('foo:bar4:bar4', 'innerStartPage', '');
        saveWikiText('foo:bar4:sidebar', 'sidebarInside', '');

        $ID = 'foo:bar4';
        $sidebar = page_findnearest('sidebar');
        $this->assertEquals('sidebar', $sidebar);

        $ID = 'foo:bar4:test';
        $sidebar = page_findnearest('sidebar');
        $this->assertEquals('foo:bar4:sidebar', $sidebar);

        $ID = 'foo:bar4:deeper:even:deeper:page';
        $sidebar = page_findnearest('sidebar');
        $this->assertEquals('foo:bar4:sidebar', $sidebar);

        saveWikiText('foo:sidebar', 'sidebarInside', '');

        $ID = 'foo:bar5:deeper:even:deeper:page';
        $sidebar = page_findnearest('sidebar');
        $this->assertEquals('foo:sidebar', $sidebar);

        saveWikiText('foo:bar5:sidebar', 'sidebarInside', '');

        $ID = 'foo:bar5:deeper:even:deeper:page';
        $sidebar = page_findnearest('sidebar');
        $this->assertEquals('foo:bar5:sidebar', $sidebar);

        // clean up to avoid issues with future tests
        saveWikiText('foo:sidebar', null, '');
    }
    
    function testExistingSidebars() {
        global $ID;

        saveWikiText('sidebar', 'topsidebar-test', '');

        $ID = 'foo:bar:baz:test';
        $sidebar = page_findnearest('sidebar');
        $this->assertEquals('sidebar', $sidebar);

        $ID = 'foo';
        $sidebar = page_findnearest('sidebar');
        $this->assertEquals('sidebar', $sidebar);

        saveWikiText('foo:bar:sidebar', 'bottomsidebar-test', '');

        $ID = 'foo:bar:baz:test';
        $sidebar = page_findnearest('sidebar');
        $this->assertEquals('foo:bar:sidebar', $sidebar);

        $ID = 'foo:bar:test';
        $sidebar = page_findnearest('sidebar');
        $this->assertEquals('foo:bar:sidebar', $sidebar);

        $ID = 'foo';
        $sidebar = page_findnearest('sidebar');
        $this->assertEquals('sidebar', $sidebar);
    }

    function testACLWithSidebar() {
        global $ID;
        global $INPUT;

        $INPUT->server->set('REMOTE_USER', 'foo');

        saveWikiText('sidebar', 'top sidebar', '');
        saveWikiText('internal:sidebar', 'internal sidebar', '');

        $ID = 'internal:foo:bar';

        $sidebar = page_findnearest('sidebar');
        $this->assertEquals('sidebar', $sidebar);

        $sidebar = page_findnearest('sidebar', false);
        $this->assertEquals('internal:sidebar', $sidebar);

        $INPUT->server->set('REMOTE_USER', 'max');

        $sidebar = page_findnearest('sidebar');
        $this->assertEquals('internal:sidebar', $sidebar);
    }
}
