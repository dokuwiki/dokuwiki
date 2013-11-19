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

        // this should abort completely
        $data = $http->get($this->server.'/stream/30');
        $this->assertTrue($data === false, 'HTTP response');

        // this should read just the needed bytes
        $http->max_bodysize_abort = false;
        $http->keep_alive = false;
        $data = $http->get($this->server.'/stream/30');
        $this->assertFalse($data === false, 'HTTP response');
        /* should read no more than max_bodysize+1 */
        $this->assertLessThanOrEqual(251,strlen($data));
    }

    /**
     * @group internet
     */
    function test_maxbodyok(){
        $http = new HTTPClient();
        $http->max_bodysize = 500*1024;
        $data = $http->get($this->server.'/stream/5');
        $this->assertTrue($data !== false, 'HTTP response');
        $http->max_bodysize_abort = false;
        $data = $http->get($this->server.'/stream/5');
        $this->assertTrue($data !== false, 'HTTP response');
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

    /**
     * @group internet
     */
    function test_chunked(){
        $http = new HTTPClient();
        $data = $http->get('http://whoopdedo.org/cgi-bin/chunked/2550');
        $this->assertFalse($data === false, 'HTTP response');
        $this->assertEquals(2550,strlen($data));
    }

    /**
     * This address caused trouble with stream_select()
     *
     * @group internet
     */
    function test_wikimatrix(){
        $http = new HTTPClient();
        $data = $http->get('http://www.wikimatrix.org/cfeed/dokuwiki/-/-');
        $this->assertTrue($data !== false, $http->error);
    }

    function test_postencode(){
        $http = new HTTPClient();


        // check simple data
        $data = array(
            'öä?' => 'öä?',
            'foo' => 'bang'
        );
        $this->assertEquals(
            '%C3%B6%C3%A4%3F=%C3%B6%C3%A4%3F&foo=bang',
            $http->_postEncode($data),
            'simple'
        );

        // check first level numeric array
        $data = array(
            'foo' => 'bang',
            'ärr' => array('ö', 'b', 'c')
        );
        $this->assertEquals(
            'foo=bang&%C3%A4rr%5B0%5D=%C3%B6&%C3%A4rr%5B1%5D=b&%C3%A4rr%5B2%5D=c',
            $http->_postEncode($data),
            'onelevelnum'
        );

        // check first level associative array
        $data = array(
            'foo' => 'bang',
            'ärr' => array('ö'=>'ä', 'b' => 'c')
        );
        $this->assertEquals(
            'foo=bang&%C3%A4rr%5B%C3%B6%5D=%C3%A4&%C3%A4rr%5Bb%5D=c',
            $http->_postEncode($data),
            'onelevelassoc'
        );


        // check first level associative array
        $data = array(
            'foo' => 'bang',
            'ärr' => array('ö'=>'ä', 'ä' => array('ö'=>'ä'))
        );
        $this->assertEquals(
            'foo=bang&%C3%A4rr%5B%C3%B6%5D=%C3%A4&%C3%A4rr%5B%C3%A4%5D%5B%C3%B6%5D=%C3%A4',
            $http->_postEncode($data),
            'twolevelassoc'
        );
    }
}
//Setup VIM: ex: et ts=4 :
