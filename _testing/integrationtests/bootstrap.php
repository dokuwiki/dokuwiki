<?php

/**
 * Integration Test Library for DokuWiki
 *
 * Simulates a full DokuWiki HTTP Request and allows
 * runtime inspection.
 */

// load dw
define('DOKU_INC', dirname(dirname(dirname(__FILE__))).'/');
require_once(DOKU_INC.'inc/init.php');

// output buffering
$output_buffer = '';

function ob_start_callback($buffer) {
	global $output_buffer;
	$output_buffer .= $buffer;
}

// Helper class to execute a fake request
class TestRequest {
	var $server_vars = array(
			'REMOTE_ADDR' => '127.0.0.1',
		);

	var $get_vars = array();
	var $post_vars = array();

	var $output = '';

	function __construct($page = '') {
		$this->setPage($page);
	}

	function setServerVar($varName, $varValue) {
		$this->sevrer_vars[$varName] = $varValue;
	}

	function setGetVar($varName, $varValue) {
		$this->get_vars[$varName] = $varValue;
	}

	function setPostVar($varName, $varValue) {
		$this->post_vars[$varName] = $varValue;
	}

	function setPage($pageName) {
		$this->setGetVar('id', $pageName);
	}

	function execute() {
		global $output_buffer;
		$output_buffer = '';

		// fake php environment
		foreach ($this->server_vars as $key => $value) {
			$_SERVER[$key] = $value;
		}
		$_REQUEST = array();
		$_GET = array();
		foreach ($this->get_vars as $key => $value) {
			$_GET[$key] = $value;
			$_REQUEST[$key] = $value;
		}
		$_POST = array();
		foreach ($this->post_vars as $key => $value) {
			$_POST[$key] = $value;
			$_REQUEST[$key] = $value;
		}

		// now execute dokuwiki and grep the output
		header_remove();
		ob_start('ob_start_callback');
		include(DOKU_INC.'doku.php');
		ob_end_flush();

		// it's done, return the page result
		return new TestResponse(
				$output_buffer,
				headers_list()
			);
	}
}

// holds a copy of all produced outputs of a TestRequest
class TestResponse {
	var $content;
	var $headers;

	function __construct($content, $headers) {
		$this->content = $content;
		$this->headers = $headers;
	}

	function getContent() {
		return $this->content;
	}

	function getHeaders() {
		return $this->headers;
	}
}
