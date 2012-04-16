<?php

require_once dirname(__FILE__).'/httpclient_http.test.php';

class httpclient_https_test extends httpclient_http_test {
    protected $server = 'https://httpbin.org/';
}
//Setup VIM: ex: et ts=4 :
