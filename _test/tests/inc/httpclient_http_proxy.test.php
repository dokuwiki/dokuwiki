<?php

require_once (__DIR__ . '/httpclient_mock.php');

class httpclient_http_proxy_test extends DokuWikiTest {
    protected $url = 'http://httpbin.org/user-agent';

    /**
     * @group internet
     */
    function test_simpleget(){
        $http = new HTTPMockClient();
        // proxy provided by  Andrwe Lord Weber <dokuwiki@andrwe.org>
        $http->proxy_host = 'proxy.andrwe.org';
        $http->proxy_port = 8080;

        $data = $http->get($this->url);
        $this->assertFalse($data === false, 'HTTP response: '.$http->error.' ['.$this->url.']');
        $this->assertTrue(strpos($data,'DokuWiki') !== false, 'response content');
    }
}