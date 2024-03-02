<?php
require_once dirname(__FILE__) . '/httpclient_http_proxy.test.php';

class httpclient_https_proxy_test extends httpclient_http_proxy_test
{
    protected $url = 'http://httpbingo.org/user-agent';

    public function setUp(): void
    {
        // skip tests when this PHP has no SSL support
        $transports = stream_get_transports();
        if (!in_array('ssl', $transports)) {
            $this->markTestSkipped('No SSL support available.');
        }
        parent::setUp();
    }
}
