<?php

class init_checkssl_test extends DokuWikiTest
{

    /**
     * Running behind an SSL proxy, HTTP between server and proxy
     * Proxy (REMOTE_ADDR) is matched by default trustedproxy config regex
     * HTTPS not set
     * HTTP_X_FORWARDED_PROTO
     * set to https
     */
    function test1a()
    {
        global $conf;
        $conf['trustedproxy'] = '^(::1|[fF][eE]80:|127\.|10\.|192\.168\.|172\.((1[6-9])|(2[0-9])|(3[0-1]))\.)';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';

        $this->assertTrue(is_ssl());
    }

    /**
     * Running behind an SSL proxy, HTTP between server and proxy
     * Proxy (REMOTE_ADDR) is not matched by default trustedproxy config regex
     * HTTPS not set
     * HTTP_X_FORWARDED_PROTO
     * set to https
     */
    function test1b()
    {
        global $conf;
        $conf['trustedproxy'] = '^(::1|[fF][eE]80:|127\.|10\.|192\.168\.|172\.((1[6-9])|(2[0-9])|(3[0-1]))\.)';
        $_SERVER['REMOTE_ADDR'] = '8.8.8.8';
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';

        $this->assertFalse(is_ssl());
    }

    /**
     * Running behind a plain HTTP proxy, HTTP between server and proxy
     * HTTPS not set
     * HTTP_X_FORWARDED_PROTO set to http
     */
    function test2()
    {
        global $conf;
        $conf['trustedproxy'] = '^(::1|[fF][eE]80:|127\.|10\.|192\.168\.|172\.((1[6-9])|(2[0-9])|(3[0-1]))\.)';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'http';

        $this->assertFalse(is_ssl());
    }

    /**
     * Running behind an SSL proxy, HTTP between server and proxy
     * HTTPS set to off,
     * HTTP_X_FORWARDED_PROTO set to https
     */
    function test3()
    {
        global $conf;
        $conf['trustedproxy'] = '^(::1|[fF][eE]80:|127\.|10\.|192\.168\.|172\.((1[6-9])|(2[0-9])|(3[0-1]))\.)';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        $_SERVER['HTTPS'] = 'off';

        $this->assertTrue(is_ssl());
    }

    /**
     * Not running behind a proxy, HTTPS server
     * HTTPS set to on,
     * HTTP_X_FORWARDED_PROTO not set
     */
    function test4()
    {
        $_SERVER['HTTPS'] = 'on';

        $this->assertTrue(is_ssl());
    }

    /**
     * Not running behind a proxy, plain HTTP server
     * HTTPS not set
     * HTTP_X_FORWARDED_PROTO not set
     */
    function test5()
    {
        $this->assertFalse(is_ssl());
    }

    /**
     * Not running behind a proxy, plain HTTP server
     * HTTPS set to off
     * HTTP_X_FORWARDED_PROTO not set
     */
    function test6()
    {
        $_SERVER['HTTPS'] = 'off';
        $this->assertFalse(is_ssl());
    }

    /**
     * Running behind an SSL proxy, SSL between proxy and HTTP server
     * HTTPS set to on,
     * HTTP_X_FORWARDED_PROTO set to https
     */
    function test7()
    {
        global $conf;
        $conf['trustedproxy'] = '^(::1|[fF][eE]80:|127\.|10\.|192\.168\.|172\.((1[6-9])|(2[0-9])|(3[0-1]))\.)';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        $_SERVER['HTTPS'] = 'on';

        $this->assertTrue(is_ssl());
    }
}
