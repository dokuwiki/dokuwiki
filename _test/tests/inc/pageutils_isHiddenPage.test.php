<?php

class inc_pageutils_isHiddenPage extends DokuWikiTest {

    function prepare($hidePages = '^:test$', $act = 'show') {
        global $conf;
        global $ACT;
        $conf['hidepages'] = $hidePages;
        $ACT = $act;
    }

    function testHiddenOff(){
        $this->prepare('');

        $this->assertFalse(isHiddenPage('test'));
    }

    function testHiddenOffAdmin(){
        $this->prepare('^:test$', 'admin');

        $this->assertFalse(isHiddenPage('test'));
    }

    function testHiddenOnMatch(){
        $this->prepare();

        $this->assertTrue(isHiddenPage('test'));
    }

    function testHiddenOnNoMatch(){
        $this->prepare();

        $this->assertFalse(isHiddenPage('another'));
    }

}
//Setup VIM: ex: et ts=4 :
