<?php

require_once(__DIR__ . '/httpclient_mock.php');

/**
 * Tests are executed against an instance of go-httpbin
 */
class httpclient_http_test extends DokuWikiTest
{
    protected $server = 'http://httpbingo.org';

    /**
     * @group internet
     */
    public function test_simpleget()
    {
        $http = new HTTPMockClient();
        $data = $http->get($this->server . '/get?foo=bar');
        if ($http->noconnection()) {
            $this->markTestSkipped('connection timed out');
        }
        $this->assertFalse($data === false, $http->errorInfo());
        $resp = json_decode($data, true);
        $this->assertTrue(is_array($resp), 'JSON response');
        $this->assertArrayHasKey('args', $resp);
        $this->assertEquals(['foo' => ['bar']], $resp['args']);
    }

    /**
     * @group internet
     */
    public function test_dget()
    {
        $http = new HTTPMockClient();
        $data = $http->dget($this->server . '/get', ['foo' => 'bar']);
        if ($http->noconnection()) {
            $this->markTestSkipped('connection timed out');
        }
        $this->assertFalse($data === false, $http->errorInfo());
        $resp = json_decode($data, true);
        $this->assertTrue(is_array($resp), 'JSON response');
        $this->assertArrayHasKey('args', $resp);
        $this->assertEquals(['foo' => ['bar']], $resp['args']);
    }

    /**
     * @group internet
     */
    public function test_gzip()
    {
        $http = new HTTPMockClient();
        $data = $http->get($this->server . '/gzip');
        if ($http->noconnection()) {
            $this->markTestSkipped('connection timed out');
        }
        $this->assertFalse($data === false, $http->errorInfo());
        $resp = json_decode($data, true);
        $this->assertTrue(is_array($resp), 'JSON response');
        $this->assertArrayHasKey('gzipped', $resp);
        $this->assertTrue($resp['gzipped']);
    }

    /**
     * @group internet
     */
    public function test_simplepost()
    {
        $http = new HTTPMockClient();
        $data = $http->post($this->server . '/post', ['foo' => 'bar']);
        if ($http->noconnection()) {
            $this->markTestSkipped('connection timed out');
        }
        $this->assertFalse($data === false, $http->errorInfo());
        $resp = json_decode($data, true);
        $this->assertTrue(is_array($resp), 'JSON response');
        $this->assertArrayHasKey('form', $resp);
        $this->assertEquals(['foo' => ['bar']], $resp['form']);
    }

    /**
     * @group internet
     */
    public function test_redirect()
    {
        $http = new HTTPMockClient();
        $data = $http->get($this->server . '/redirect/3');
        if ($http->noconnection()) {
            $this->markTestSkipped('connection timed out');
        }
        $this->assertFalse($data === false, $http->errorInfo());
        $resp = json_decode($data, true);
        $this->assertTrue(is_array($resp), 'JSON response');
        $this->assertArrayHasKey('url', $resp);
        $this->assertMatchesRegularExpression('/\/get$/', $resp['url']);
    }

    /**
     * @group internet
     */
    public function test_relredirect()
    {
        $http = new HTTPMockClient();
        $data = $http->get($this->server . '/relative-redirect/3');
        if ($http->noconnection()) {
            $this->markTestSkipped('connection timed out');
        }
        $this->assertFalse($data === false, $http->errorInfo());
        $resp = json_decode($data, true);
        $this->assertTrue(is_array($resp), 'JSON response');
        $this->assertArrayHasKey('url', $resp);
        $this->assertMatchesRegularExpression('/\/get$/', $resp['url']);
    }

    /**
     * @group internet
     */
    public function test_redirectfail()
    {
        $http = new HTTPMockClient();
        $data = $http->get($this->server . '/redirect/5');
        if ($http->noconnection()) {
            $this->markTestSkipped('connection timed out');
        }
        $this->assertTrue($data === false, $http->errorInfo());
        $this->assertEquals('Maximum number of redirects exceeded', $http->error);
    }

    /**
     * @group internet
     */
    public function test_cookies()
    {
        $http = new HTTPMockClient();
        $http->get($this->server . '/cookies/set?foo=bar');
        if ($http->noconnection()) {
            $this->markTestSkipped('connection timed out');
        }
        $this->assertEquals(['foo' => 'bar'], $http->cookies);
        $data = $http->get($this->server . '/cookies');
        if ($http->noconnection()) {
            $this->markTestSkipped('connection timed out');
        }
        $this->assertFalse($data === false, $http->errorInfo());
        $resp = json_decode($data, true);
        $this->assertTrue(is_array($resp), 'JSON response');
        $this->assertEquals(['foo' => 'bar'], $resp);
    }

    /**
     * @group internet
     */
    public function test_teapot()
    {
        $http = new HTTPMockClient();
        $data = $http->get($this->server . '/status/418');
        if ($http->noconnection()) {
            $this->markTestSkipped('connection timed out');
        }
        $this->assertTrue($data === false, $http->errorInfo());
        $this->assertEquals(418, $http->status);
    }

    /**
     * @group internet
     */
    public function test_maxbody()
    {
        $http = new HTTPMockClient();
        $http->max_bodysize = 250;

        // this should abort completely
        $data = $http->get($this->server . '/stream/30');
        if ($http->noconnection()) {
            $this->markTestSkipped('connection timed out');
        }
        $this->assertTrue($data === false, $http->errorInfo());

        // this should read just the needed bytes
        $http->max_bodysize_abort = false;
        $http->keep_alive = false;
        $data = $http->get($this->server . '/stream/30');
        if ($http->noconnection()) {
            $this->markTestSkipped('connection timed out');
        }
        $this->assertFalse($data === false, $http->errorInfo());
        /* should read no more than max_bodysize+1 */
        $this->assertLessThanOrEqual(251, strlen($data));
    }

    /**
     * @group internet
     */
    public function test_maxbodyok()
    {
        $http = new HTTPMockClient();
        $http->max_bodysize = 500 * 1024;
        $data = $http->get($this->server . '/stream/5');
        if ($http->noconnection()) {
            $this->markTestSkipped('connection timed out');
        }
        $this->assertTrue($data !== false, $http->errorInfo());
        $http->max_bodysize_abort = false;
        $data = $http->get($this->server . '/stream/5');
        if ($http->noconnection()) {
            $this->markTestSkipped('connection timed out');
        }
        $this->assertTrue($data !== false, $http->errorInfo());
    }

    /**
     * @group internet
     */
    function test_basicauth()
    {
        $http = new HTTPMockClient();
        $http->user = 'user';
        $http->pass = 'pass';
        $data = $http->get($this->server . '/basic-auth/user/pass');
        if ($http->noconnection()) {
            $this->markTestSkipped('connection timed out');
        }
        $this->assertFalse($data === false, $http->errorInfo());
        $resp = json_decode($data, true);
        $this->assertTrue(is_array($resp), 'JSON response');
        $this->assertEquals(['authorized' => true, 'user' => 'user'], $resp);
    }

    /**
     * @group internet
     */
    public function test_basicauthfail()
    {
        $http = new HTTPMockClient();
        $http->user = 'user';
        $http->pass = 'invalid';
        $data = $http->get($this->server . '/basic-auth/user/pass');
        if ($http->noconnection()) {
            $this->markTestSkipped('connection timed out');
        }
        $this->assertTrue($data === false, $http->errorInfo());
        $this->assertEquals(401, $http->status);
    }

    /**
     * @group internet
     */
    public function test_timeout()
    {
        $http = new HTTPMockClient();
        $http->timeout = 5;
        $data = $http->get($this->server . '/delay/10');
        $this->assertTrue($data === false, $http->errorInfo());
        $this->assertEquals(-100, $http->status);
    }

    /**
     * @group internet
     */
    public function test_headers()
    {
        $http = new HTTPMockClient();
        $data = $http->get($this->server . '/response-headers?baz=&foo=bar');
        if ($http->noconnection()) {
            $this->markTestSkipped('connection timed out');
        }
        $this->assertFalse($data === false, $http->errorInfo());
        $resp = json_decode($data, true);
        $this->assertTrue(is_array($resp), 'JSON response');
        $this->assertArrayHasKey('baz', $http->resp_headers);
        $this->assertArrayHasKey('foo', $http->resp_headers);
        $this->assertEquals('bar', $http->resp_headers['foo']);
    }

    /**
     * @group internet
     */
    public function test_chunked()
    {
        $http = new HTTPMockClient();
        $data = $http->get($this->server . '/stream-bytes/5000?chunk_size=250');
        if ($http->noconnection()) {
            $this->markTestSkipped('connection timed out');
        }
        $this->assertFalse($data === false, $http->errorInfo());
        $this->assertEquals(5000, strlen($data));
    }

    /**
     * This address caused trouble with stream_select()
     *
     * @group internet
     * @group flaky
     */
    public function test_wikimatrix()
    {
        $http = new HTTPMockClient();
        $data = $http->get('http://www.wikimatrix.org/cfeed/dokuwiki/-/-');
        if ($http->noconnection()) {
            $this->markTestSkipped('connection timed out');
        }
        $this->assertTrue($data !== false, $http->errorInfo());
    }

    /**
     * @throws ReflectionException
     */
    public function test_postencode()
    {
        $http = new HTTPMockClient();

        // check simple data
        $data = [
            'öä?' => 'öä?',
            'foo' => 'bang',
        ];
        $this->assertEquals(
            '%C3%B6%C3%A4%3F=%C3%B6%C3%A4%3F&foo=bang',
            $this->callInaccessibleMethod($http, 'postEncode', [$data]),
            'simple'
        );

        // check first level numeric array
        $data = [
            'foo' => 'bang',
            'ärr' => ['ö', 'b', 'c'],
        ];
        $this->assertEquals(
            'foo=bang&%C3%A4rr%5B0%5D=%C3%B6&%C3%A4rr%5B1%5D=b&%C3%A4rr%5B2%5D=c',
            $this->callInaccessibleMethod($http, 'postEncode', [$data]),
            'onelevelnum'
        );

        // check first level associative array
        $data = [
            'foo' => 'bang',
            'ärr' => ['ö' => 'ä', 'b' => 'c'],
        ];
        $this->assertEquals(
            'foo=bang&%C3%A4rr%5B%C3%B6%5D=%C3%A4&%C3%A4rr%5Bb%5D=c',
            $this->callInaccessibleMethod($http, 'postEncode', [$data]),
            'onelevelassoc'
        );

        // check first level associative array
        $data = [
            'foo' => 'bang',
            'ärr' => ['ö' => 'ä', 'ä' => ['ö' => 'ä']],
        ];
        $this->assertEquals(
            'foo=bang&%C3%A4rr%5B%C3%B6%5D=%C3%A4&%C3%A4rr%5B%C3%A4%5D%5B%C3%B6%5D=%C3%A4',
            $this->callInaccessibleMethod($http, 'postEncode', [$data]),
            'twolevelassoc'
        );
    }
}
