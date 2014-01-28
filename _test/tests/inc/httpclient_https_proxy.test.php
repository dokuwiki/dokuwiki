<?php
require_once dirname(__FILE__).'/httpclient_http_proxy.test.php';

class httpclient_https_proxy_test extends httpclient_http_proxy_test {
    protected $url = 'https://www.dokuwiki.org/README';

    public function setUp(){
    	$this->markTestSkipped('The test was skipped, because of Proxy Timeouts.');

        // skip tests when this PHP has no SSL support
        $transports = stream_get_transports();
        if(!in_array('ssl',$transports)){
            $this->markTestSkipped('No SSL support available.');
        }
        parent::setUp();
    }
}