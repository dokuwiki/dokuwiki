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

// prepare temporary directories
define('DOKU_INC', dirname(dirname(__FILE__)).'/');
define('TMP_DIR', '/tmp/dwtests-'.microtime(true));
define('DOKU_CONF', TMP_DIR.'/conf/');
define('DOKU_TMP_DATA', TMP_DIR.'/data/');

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

// default server variables
$default_server_vars = array(
    'QUERY_STRING' => '?id=',
    'REQUEST_METHOD' => 'GET',
    'CONTENT_TYPE' => '',
    'CONTENT_LENGTH' => '',
    'SCRIPT_NAME' => '/doku.php',
    'REQUEST_URI' => '/doku.php?id=',
    'DOCUMENT_URI' => '/doku.php',
    'DOCUMENT_ROOT' => DOKU_INC,
    'SERVER_PROTOCOL' => 'HTTP/1.1',
    'SERVER_SOFTWARE' => 'nginx/0.7.67',
    'REMOTE_ADDR' => '87.142.120.6',
    'REMOTE_PORT' => '21418',
    'SERVER_ADDR' => '46.38.241.24',
    'SERVER_PORT' => '443',
    'SERVER_NAME' => 'wiki.example.com',
    'REDIRECT_STATUS' => '200',
    'SCRIPT_FILENAME' => DOKU_INC.'doku.php',
    'HTTP_HOST' => 'wiki.example.com',
    'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; OpenBSD amd64; rv:11.0) Gecko/20100101 Firefox/11.0',
    'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.5',
    'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
    'HTTP_CONNECTION' => 'keep-alive',
    'HTTP_CACHE_CONTROL' => 'max-age=0',
    'PHP_SELF' => '/doku.php',
    'REQUEST_TIME' => time(),
);

// create temp directories
mkdir(TMP_DIR);

// cleanup dir after exit
register_shutdown_function(function() {
    rdelete(TMP_DIR);
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

// setup default global variables
$_GET = array('id' => '');
$_POST = array();
$_REQUEST = array('id' => '');
foreach ($default_server_vars as $key => $value) {
    $_SERVER[$key] = $value;
}

// load dw
require_once(DOKU_INC.'inc/init.php');

// output buffering
$output_buffer = '';

function ob_start_callback($buffer) {
    global $output_buffer;
    $output_buffer .= $buffer;
}

// Helper class to provide basic functionality for tests
abstract class DokuWikiTest extends PHPUnit_Framework_TestCase {
    // nothing for now, makes migration easy

    function setUp() {
        // reload config
        global $conf, $config_cascade;
        $conf = array();
        foreach (array('default','local','protected') as $config_group) {
            if (empty($config_cascade['main'][$config_group])) continue;
            foreach ($config_cascade['main'][$config_group] as $config_file) {
                if (@file_exists($config_file)) {
                    include($config_file);
                }
            }
        }

        // reload license config
        global $license;
        $license = array();

        // load the license file(s)
        foreach (array('default','local') as $config_group) {
            if (empty($config_cascade['license'][$config_group])) continue;
            foreach ($config_cascade['license'][$config_group] as $config_file) {
                if(@file_exists($config_file)){
                    include($config_file);
                }
            }
        }

        // make real paths and check them
        init_paths();
        init_files();

        // reset loaded plugins
        global $plugin_controller_class, $plugin_controller;
        $plugin_controller = new $plugin_controller_class();
        global $EVENT_HANDLER;
        $EVENT_HANDLER = new Doku_Event_Handler();

        // reload language
        $local = $conf['lang'];
        trigger_event('INIT_LANG_LOAD', $local, 'init_lang', true);
    }

}

// Helper class to execute a fake request
class TestRequest {

    function execute() {
        global $output_buffer;
        $output_buffer = '';

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
