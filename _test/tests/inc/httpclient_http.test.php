<?php

class httpclient_http_test extends DokuWikiTest {
    protected $server = 'http://httpbin.org';

    /**
     * @group internet
     */
    function test_simpleget(){
        $http = new HTTPClient();
        $data = $http->get($this->server.'/get?foo=bar');
        $this->assertFalse($data === false, 'HTTP response');
        $resp = json_decode($data, true);
        $this->assertTrue(is_array($resp), 'JSON response');
        $this->assertArrayHasKey('args',$resp);
        $this->assertEquals(array('foo'=>'bar'), $resp['args']);
    }

    /**
     * @group internet
     */
    function test_dget(){
        $http = new HTTPClient();
        $data = $http->dget($this->server.'/get',array('foo'=>'bar'));
        $this->assertFalse($data === false, 'HTTP response');
        $resp = json_decode($data, true);
        $this->assertTrue(is_array($resp), 'JSON response');
        $this->assertArrayHasKey('args',$resp);
        $this->assertEquals(array('foo'=>'bar'), $resp['args']);
    }

    /**
     * @group internet
     */
    function test_gzip(){
        $http = new HTTPClient();
        $data = $http->get($this->server.'/gzip');
        $this->assertFalse($data === false, 'HTTP response');
        $resp = json_decode($data, true);
        $this->assertTrue(is_array($resp), 'JSON response');
        $this->assertArrayHasKey('gzipped',$resp);
        $this->assertTrue($resp['gzipped']);
    }

    /**
     * @group internet
     */
    function test_simplepost(){
        $http = new HTTPClient();
        $data = $http->post($this->server.'/post',array('foo'=>'bar'));
        $this->assertFalse($data === false, 'HTTP response');
        $resp = json_decode($data, true);
        $this->assertTrue(is_array($resp), 'JSON response');
        $this->assertArrayHasKey('form',$resp);
        $this->assertEquals(array('foo'=>'bar'), $resp['form']);
    }

    /**
     * @group internet
     */
    function test_redirect(){
        $http = new HTTPClient();
        $data = $http->get($this->server.'/redirect/3');
        $this->assertFalse($data === false, 'HTTP response');
        $resp = json_decode($data, true);
        $this->assertTrue(is_array($resp), 'JSON response');
        $this->assertArrayHasKey('url',$resp);
        $this->assertRegExp('/\/get$/', $resp['url']);
    }

    /**
     * @group internet
     */
    function test_relredirect(){
        $http = new HTTPClient();
        $data = $http->get($this->server.'/relative-redirect/3');
        $this->assertFalse($data === false, 'HTTP response');
        $resp = json_decode($data, true);
        $this->assertTrue(is_array($resp), 'JSON response');
        $this->assertArrayHasKey('url',$resp);
        $this->assertRegExp('/\/get$/', $resp['url']);
    }

    /**
     * @group internet
     */
    function test_redirectfail(){
        $http = new HTTPClient();
        $data = $http->get($this->server.'/redirect/5');
        $this->assertTrue($data === false, 'HTTP response');
        $this->assertEquals('Maximum number of redirects exceeded',$http->error);
    }

    /**
     * @group internet
     */
    function test_cookies(){
        $http = new HTTPClient();
        $http->get($this->server.'/cookies/set/foo/bar');
        $this->assertEquals(array('foo' => 'bar'), $http->cookies);
        $data = $http->get($this->server.'/cookies');
        $this->assertFalse($data === false, 'HTTP response');
        $resp = json_decode($data, true);
        $this->assertTrue(is_array($resp), 'JSON response');
        $this->assertArrayHasKey('cookies',$resp);
        $this->assertEquals(array('foo'=>'bar'), $resp['cookies']);
    }

    /**
     * @group internet
     */
    function test_teapot(){
        $http = new HTTPClient();
        $data = $http->get($this->server.'/status/418');
        $this->assertTrue($data === false, 'HTTP response');
        $this->assertEquals(418,$http->status);
    }

    /**
     * @group internet
     */
    function test_maxbody(){
        $http = new HTTPClient();
        $http->max_bodysize = 250;
        $data = $http->get($this->server.'/stream/30');
        $this->assertTrue($data === false, 'HTTP response');
    }

    /**
     * @group internet
     */
    function test_basicauth(){
        $http = new HTTPClient();
        $http->user = 'user';
        $http->pass = 'pass';
        $data = $http->get($this->server.'/basic-auth/user/pass');
        $this->assertFalse($data === false, 'HTTP response');
        $resp = json_decode($data, true);
        $this->assertTrue(is_array($resp), 'JSON response');
        $this->assertEquals(array('authenticated'=>true,'user'=>'user'), $resp);
    }

    /**
     * @group internet
     */
    function test_basicauthfail(){
        $http = new HTTPClient();
        $http->user = 'user';
        $http->pass = 'invalid';
        $data = $http->get($this->server.'/basic-auth/user/pass');
        $this->assertTrue($data === false, 'HTTP response');
        $this->assertEquals(401,$http->status);
    }

    /**
     * @group internet
     */
    function test_timeout(){
        $http = new HTTPClient();
        $http->timeout = 5;
        $data = $http->get($this->server.'/delay/10');
        $this->assertTrue($data === false, 'HTTP response');
        $this->assertEquals(-100,$http->status);
    }

    /**
     * @group internet
     */
    function test_headers(){
        $http = new HTTPClient();
        $data = $http->get($this->server.'/response-headers?baz=&foo=bar');
        $this->assertFalse($data === false, 'HTTP response');
        $resp = json_decode($data, true);
        $this->assertTrue(is_array($resp), 'JSON response');
        $this->assertArrayHasKey('baz',$http->resp_headers);
        $this->assertArrayHasKey('foo',$http->resp_headers);
        $this->assertEquals('bar',$http->resp_headers['foo']);
    }
}
//Setup VIM: ex: et ts=4 :
