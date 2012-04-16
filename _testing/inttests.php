<?php

/**
 * Integration Test Library for DokuWiki
 *
 * Simulates a full DokuWiki HTTP Request and allows
 * runtime inspection.
 */

// helper for recursive copy()
function rcopy($destdir, $source) {
	if (!is_dir($source)) {
		copy($source, $destdir.'/'.basename($source));
	} else {
		$newdestdir = $destdir.'/'.basename($source);
		mkdir($newdestdir);

		$dh = dir($source);
		while (false !== ($entry = $dh->read())) {
			if ($entry == '.' || $entry == '..') {
				continue;
			}
			rcopy($newdestdir, $source.'/'.$entry);
		}
		$dh->close();
	}
}

// prepare temporary directories
define('DOKU_INC', dirname(dirname(__FILE__)).'/');
define('TMP_DIR', '/tmp/dwinttests-'.microtime(true));
define('DOKU_CONF', TMP_DIR.'/inttests.conf/');
define('DOKU_PLUGIN', TMP_DIR.'/plugins/');
define('DOKU_TMP_DATA', TMP_DIR.'/inttests.data/');

// create temp directories
mkdir(TMP_DIR);
mkdir(DOKU_PLUGIN);

// populate default dirs
rcopy(TMP_DIR, dirname(__FILE__).'/inttests.conf');
rcopy(TMP_DIR, dirname(__FILE__).'/inttests.data');

// load dw
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

	function addLocalConf($text) {
		$this->conf_local[] = $text;
	}

	function hook($hook, $step, $function) {
		global $EVENT_HANDLER;
		$null = null;
		$EVENT_HANDLER->register_hook($hook, $step, $null, $function);
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
