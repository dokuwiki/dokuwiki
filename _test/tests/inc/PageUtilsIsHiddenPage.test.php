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

    function testEventHandlerBefore() {
        global $EVENT_HANDLER;
        $this->prepare();
        $EVENT_HANDLER->register_hook('PAGEUTILS_ID_HIDEPAGE', 'BEFORE', $this, 'alwaysHide');

        $this->assertTrue(isHiddenPage('another'));
    }

    function alwaysHide(Doku_Event &$event, $params) {
        $event->data['hidden'] = true;
    }

    function testEventHandlerBeforeAndPrevent() {
        global $EVENT_HANDLER;
        $this->prepare();
        $EVENT_HANDLER->register_hook('PAGEUTILS_ID_HIDEPAGE', 'BEFORE', $this, 'showBefore');

        $this->assertFalse(isHiddenPage('test'));
    }

    function showBefore(Doku_Event &$event, $params) {
        $event->data['hidden'] = false;
        $event->preventDefault();
        $event->stopPropagation();
    }

    function testEventHandlerAfter() {
        global $EVENT_HANDLER;
        $this->prepare();
        $EVENT_HANDLER->register_hook('PAGEUTILS_ID_HIDEPAGE', 'AFTER', $this, 'alwaysHide');

        $this->assertTrue(isHiddenPage('another'));
    }

    function testEventHandlerAfterHide() {
        global $EVENT_HANDLER;
        $this->prepare();
        $EVENT_HANDLER->register_hook('PAGEUTILS_ID_HIDEPAGE', 'AFTER', $this, 'hideBeforeWithoutPrevent');

        $this->assertTrue(isHiddenPage('another'));
    }

    function hideBeforeWithoutPrevent(Doku_Event &$event, $params) {
        $event->data['hidden'] = true;
    }

    function testEventHandlerAfterShow() {
        global $EVENT_HANDLER;
        $this->prepare();
        $EVENT_HANDLER->register_hook('PAGEUTILS_ID_HIDEPAGE', 'AFTER', $this, 'showAfter');

        $this->assertFalse(isHiddenPage('test'));
    }

    function showAfter(Doku_Event &$event, $params) {
        $event->data['hidden'] = false;
    }

}
//Setup VIM: ex: et ts=4 :
