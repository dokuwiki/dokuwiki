<?php

class init_checkssl_test extends DokuWikiTest {

	/**
	 * Running behind an SSL proxy, HTTP between server and proxy
	 * HTTPS not set
	 * HTTP_X_FORWARDED_PROTO
	 * set to https
	 */
	function test1() {
		$_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';

		$this->assertEquals(is_ssl(), true);
	}

	/**
	 * Running behind a plain HTTP proxy, HTTP between server and proxy
	 * HTTPS not set
	 * HTTP_X_FORWARDED_PROTO set to http
	 */
	function test2() {
		$_SERVER['HTTP_X_FORWARDED_PROTO'] = 'http';

		$this->assertEquals(is_ssl(), false);
	}

	/**
	 * Running behind an SSL proxy, HTTP between server and proxy
	 * HTTPS set to off,
	 * HTTP_X_FORWARDED_PROTO set to https
	 */
	function test3() {
		$_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
		$_SERVER['HTTPS'] = 'off';

		$this->assertEquals(is_ssl(), true);
	}

	/**
	 * Not running behind a proxy, HTTPS server
	 * HTTPS set to on,
	 * HTTP_X_FORWARDED_PROTO not set
	 */
	function test4() {
		$_SERVER['HTTPS'] = 'on';

		$this->assertEquals(is_ssl(), true);
	}

	/**
	 * Not running behind a proxy, plain HTTP server
	 * HTTPS not set
	 * HTTP_X_FORWARDED_PROTO not set
	 */
	function test5() {
		$this->assertEquals(is_ssl(), false);
	}

	/**
	 * Not running behind a proxy, plain HTTP server
	 * HTTPS set to off
	 * HTTP_X_FORWARDED_PROTO not set
	 */
	function test6() {
		$_SERVER['HTTPS'] = 'off';
		$this->assertEquals(is_ssl(), false);
	}

	/**
	 * Running behind an SSL proxy, SSL between proxy and HTTP server
	 * HTTPS set to on,
	 * HTTP_X_FORWARDED_PROTO set to https
	 */
	function test7() {
		$_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
		$_SERVER['HTTPS'] = 'on';

		$this->assertEquals(is_ssl(), true);
	}
}
