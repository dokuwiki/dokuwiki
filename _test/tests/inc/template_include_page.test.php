<?php

class template_include_page_test extends DokuWikiTest {
    function testNoSidebar() {
        global $ID;

        $ID = 'foo:bar:baz:test';
        $sidebar = tpl_include_page('sidebar', false, true);
        $this->assertEquals('', $sidebar);
    }

    function testExistingSidebars() {
        global $ID;

        saveWikiText('sidebar', 'topsidebar-test', '');

        $ID = 'foo:bar:baz:test';
        $sidebar = tpl_include_page('sidebar', false, true);
        $this->assertTrue(strpos($sidebar, 'topsidebar-test') > 0);

        $ID = 'foo';
        $sidebar = tpl_include_page('sidebar', false, true);
        $this->assertTrue(strpos($sidebar, 'topsidebar-test') > 0);

        saveWikiText('foo:bar:sidebar', 'bottomsidebar-test', '');

        $ID = 'foo:bar:baz:test';
        $sidebar = tpl_include_page('sidebar', false, true);
        $this->assertTrue(strpos($sidebar, 'bottomsidebar-test') > 0);

        $ID = 'foo:bar:test';
        $sidebar = tpl_include_page('sidebar', false, true);
        $this->assertTrue(strpos($sidebar, 'bottomsidebar-test') > 0);

        $ID = 'foo';
        $sidebar = tpl_include_page('sidebar', false, true);
        $this->assertTrue(strpos($sidebar, 'topsidebar-test') > 0);
    }

}
