<?php

class httpclient_http_proxy_test extends DokuWikiTest {
    protected $url = 'http://www.dokuwiki.org/README';

    /**
     * @group internet
     */
    function test_simpleget(){
        $http = new HTTPClient();
        $http->proxy_host = 'localhost'; //FIXME we need a public server
        $http->proxy_port = 3128;

        $data = $http->get($this->url);
        $this->assertFalse($data === false, 'HTTP response');
        $this->assertTrue(strpos($data,'DokuWiki') !== false, 'response content');
    }

}