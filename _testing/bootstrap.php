<?php
/**
 * Test Library for DokuWiki
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

// helper for recursive rmdir()/unlink()
function rdelete($target) {
	if (!is_dir($target)) {
		unlink($target);
	} else {
		$dh = dir($target);
		while (false !== ($entry = $dh->read())) {
			if ($entry == '.' || $entry == '..') {
				continue;
			}
			rdelete("$target/$entry");
		}
		$dh->close();
		rmdir($target);
	}
}

// helper to append text to a file
function fappend($file, $text) {
	$fh = fopen($file, 'a');
	fwrite($fh, $text);
	fclose($fh);
}

// if someone really wants a special handling during tests
define('DOKU_UNITTEST', true);
define('SIMPLE_TEST', true);

// basic behaviours
error_reporting(E_ALL);
set_time_limit(0);
ini_set('memory_limit','2048M');

// default plugins
$default_plugins = array(
	'acl',
	'action',
	'admin',
	'config',
	'info',
	'plugin',
	'popularity',
	'remote',
	'revert',
	'safefnrecode',
	'syntax',
	'usermanager'
);

// prepare temporary directories
define('DOKU_INC', dirname(dirname(__FILE__)).'/');
define('TMP_DIR', '/tmp/dwtests-'.microtime(true));
define('DOKU_CONF', TMP_DIR.'/conf/');
define('DOKU_TMP_DATA', TMP_DIR.'/data/');

// create temp directories
mkdir(TMP_DIR);

// cleanup dir after exit
register_shutdown_function(function() {
//	rdelete(TMP_DIR);
});

// populate default dirs
rcopy(TMP_DIR, dirname(__FILE__).'/conf');
rcopy(TMP_DIR, dirname(__FILE__).'/data');

// disable all non-default plugins by default
$dh = dir(DOKU_INC.'lib/plugins/');
while (false !== ($entry = $dh->read())) {
	if ($entry == '.' || $entry == '..' || $entry == 'index.html') {
		continue;
	}

	if (substr($entry, strlen($entry) - 4) == '.php') {
		$plugin = substr($entry, 0, strlen($entry) - 4);
	} else {
		$plugin = $entry;
	}

	if (!in_array($plugin, $default_plugins)) {
		// disable this plugin
		fappend(DOKU_CONF.'plugins.local.php', "\$plugins['$plugin'] = 0;\n");
	}
}
$dh->close();

// TODO setup default global variables
$_SERVER['REMOTE_ADDR'] = '173.194.69.138';

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

	// TODO provide findById, findBy... (https://github.com/cosmocode/dokuwiki-plugin-scrape/blob/master/phpQuery-onefile.php)
}
