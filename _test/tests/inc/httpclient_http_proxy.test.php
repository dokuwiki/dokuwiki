<?php

require_once(__DIR__ . '/httpclient_mock.php');

class httpclient_http_proxy_test extends DokuWikiTest
{
    protected $url = 'http://httpbingo.org/user-agent';
    protected $host;
    protected $port;


    public function setUp(): void
    {
        parent::setUp();
        $configuration = DOKU_UNITTEST . "proxy.conf.php";
        if (file_exists($configuration)) {
            /** @var $conf array */
            include $configuration;
            $this->host = $conf['host'];
            $this->port = $conf['port'];
        }

        if (!$this->host || !$this->port) {
            $this->markTestSkipped("Skipped proxy tests. Missing configuration");
        }
    }


    /**
     * @group internet
     */
    function testSimpleGet()
    {
        $http = new HTTPMockClient();
        $http->proxy_host = $this->host;
        $http->proxy_port = $this->port;

        $data = $http->get($this->url);
        $this->assertFalse($data === false, $http->errorInfo($this->url));
        $this->assertStringContainsString('DokuWiki', $data, 'response content');
    }
}
