<?php

class template_pagetitle_test extends DokuWikiTest {

    function test_localID() {
        global $ID,$ACT;


        $id = 'foo:bar';

        $ACT = 'show';
        $this->assertEquals('foo:bar', tpl_pagetitle($id, true));
    }

    function test_globalID() {
        global $ID,$ACT;


        $ID = 'foo:bar';

        $ACT = 'show';
        $this->assertEquals('foo:bar', tpl_pagetitle(null, true));
    }

    function test_adminTitle() {
        global $ID,$ACT;

        $ID = 'foo:bar';

        $ACT = 'admin';
        $this->assertEquals('Admin', tpl_pagetitle(null, true));
    }

    function test_adminPluginTitle() {
        global $ID,$ACT,$INPUT,$conf;

        if (!plugin_load('admin','revert')) {
            $this->markTestSkipped('Revert plugin not found, unable to test admin plugin titles');
            return;
        }

        $ID = 'foo:bar';
        $ACT = 'admin';
        $conf['lang'] = 'en';
        $INPUT->set('page','revert');

        $this->assertEquals('Revert Manager', tpl_pagetitle(null, true));
    }

    function test_nonPageFunctionTitle() {
        global $ID,$ACT;

        $ID = 'foo:bar';

        $ACT = 'index';
        $this->assertEquals('Sitemap', tpl_pagetitle(null, true));
    }

    function test_pageFunctionTitle() {
        global $ID,$ACT;

        $ID = 'foo:bar';

        $ACT = 'revisions';
        $this->assertEquals('foo:bar - Old revisions', tpl_pagetitle(null, true));
    }
}
