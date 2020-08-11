<?php

require_once __DIR__ . '/httpclient_mock.php';
require_once __DIR__ .'/httpclient_http.test.php';

class httpclient_http_proxy_test extends httpclient_http_test {
    protected $url = 'http://eu.httpbin.org/user-agent';
    protected $useproxy = true;
}
