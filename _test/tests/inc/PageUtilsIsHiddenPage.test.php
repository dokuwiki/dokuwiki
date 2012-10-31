<?php

class PageUtilsIsHiddenPageTest extends DokuWikiTest {

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

    function testEventHandler() {
        global $EVENT_HANDLER;
        $this->prepare();
        $EVENT_HANDLER->register_hook('PAGEUTILS_ID_HIDEPAGE', 'BEFORE', $this, 'alwaysHide');

        $this->assertFalse(isHiddenPage('test'));
    }

    function alwaysHide(Doku_Event &$event, $params) {
        $event->data['hide'] = true;
    }

}
//Setup VIM: ex: et ts=4 :
