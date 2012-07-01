<?php

class pageutils_findnearest_test extends DokuWikiTest {
    function testNoSidebar() {
        global $ID;

        $ID = 'foo:bar:baz:test';
        $sidebar = page_findnearest('sidebar');
        $this->assertEquals(false, $sidebar);
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

}
