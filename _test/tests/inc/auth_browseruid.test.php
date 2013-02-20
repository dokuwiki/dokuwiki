<?php

class auth_browseruid_test extends DokuWikiTest {


    /**
     * regression test to ensure correct browser id on IE9.
     *
     * IE9 send different HTTP_ACCEPT_LANGUAGE header on ajax request.
     */
    function testIE9JsVsDefault() {

        // javascript request
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)';
        $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip, deflate';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de';
        unset($_SERVER['HTTP_ACCEPT_CHARSET']);
        $javascriptId = auth_browseruid();

        // default request
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)';
        $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip, deflate';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de-DE';
        $normalId = auth_browseruid();

        $this->assertEquals($normalId, $javascriptId);

    }

}