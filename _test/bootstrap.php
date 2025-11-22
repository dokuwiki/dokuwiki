<?php
/**
 * Test Suite bootstrapping for EasyWiki
 */

if(!defined('WIKI_UNITTEST')) define('WIKI_UNITTEST',dirname(__FILE__).'/');
require_once WIKI_UNITTEST.'vendor/autoload.php';
require_once WIKI_UNITTEST.'core/phpQuery-onefile.php'; // deprecated
require_once WIKI_UNITTEST.'core/EasyWikiTest.php';
require_once WIKI_UNITTEST.'core/TestResponse.php';
require_once WIKI_UNITTEST.'core/TestRequest.php';
require_once WIKI_UNITTEST.'core/TestUtils.php';


// backward compatibility to old test suite
define('SIMPLE_TEST', true);

// basic behaviours
define('WIKI_E_LEVEL',E_ALL ^ E_NOTICE);
error_reporting(WIKI_E_LEVEL);
set_time_limit(0);
ini_set('memory_limit','2048M');

// prepare temporary directories; str_replace is for WIN
define('WIKI_INC', str_replace('\\', '/', dirname(dirname(__FILE__))) . '/');
define('TMP_DIR', str_replace('\\', '/', sys_get_temp_dir()) . '/dwtests-'.microtime(true));
define('WIKI_CONF', TMP_DIR.'/conf/');
define('DOKU_TMP_DATA', TMP_DIR.'/data/');

// default plugins
$default_plugins = array(
    'authplain',
    'acl',
    'config',
    'info',
    'plugin',
    'popularity',
    'revert',
    'safefnrecode',
    'usermanager'
);

// default server variables
$default_server_vars = array(
    'QUERY_STRING' => '?id=',
    'REQUEST_METHOD' => 'GET',
    'CONTENT_TYPE' => '',
    'CONTENT_LENGTH' => '',
    'SCRIPT_NAME' => '/wiki.php',
    'REQUEST_URI' => '/wiki.php?id=',
    'DOCUMENT_URI' => '/wiki.php',
    'DOCUMENT_ROOT' => WIKI_INC,
    'SERVER_PROTOCOL' => 'HTTP/1.1',
    'SERVER_SOFTWARE' => 'nginx/0.7.67',
    'REMOTE_ADDR' => '172.17.18.19',
    'REMOTE_PORT' => '21418',
    'SERVER_ADDR' => '10.11.12.13',
    'SERVER_PORT' => '80',
    'SERVER_NAME' => 'wiki.example.com',
    'REDIRECT_STATUS' => '200',
    'SCRIPT_FILENAME' => WIKI_INC.'wiki.php',
    'HTTP_HOST' => 'wiki.example.com',
    'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; OpenBSD amd64; rv:11.0) Gecko/20100101 Firefox/11.0',
    'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.5',
    'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
    'HTTP_CONNECTION' => 'keep-alive',
    'HTTP_CACHE_CONTROL' => 'max-age=0',
    'PHP_SELF' => '/wiki.php',
    'REQUEST_TIME' => time(),
);

// fixup for $_SERVER when run from CLI,
// some values should be mocked for use by inc/init.php which is called here
// [ $_SERVER is also mocked in TestRequest::execute() ]
if (php_sapi_name() == 'cli') {
  $_SERVER = array_merge($default_server_vars, $_SERVER);
}

// create temp directories
mkdir(TMP_DIR);

// cleanup dir after exit
if (getenv('PRESERVE_TMP') != 'true') {
    register_shutdown_function(function() {
        TestUtils::rdelete(TMP_DIR);
    });
} else {
    echo ">>>> Preserving temporary directory: ".TMP_DIR."\n";
}

// populate default dirs for initial setup
EasyWikiTest::setupDataDir();
EasyWikiTest::setupConfDir();

// disable all non-default plugins by default
$dh = dir(WIKI_INC.'lib/plugins/');
while (false !== ($entry = $dh->read())) {
    if ($entry == '.' || $entry == '..') {
        continue;
    }

    if (!is_dir(WIKI_INC.'lib/plugins/'.$entry)) {
        continue;
    }

    if (!in_array($entry, $default_plugins)) {
        // disable this plugin
        TestUtils::fappend(WIKI_CONF.'plugins.local.php', "\$plugins['$entry'] = 0;\n");
    }
}
$dh->close();

// use no mbstring help during tests
if (!defined('UTF8_NOMBSTRING')) define('UTF8_NOMBSTRING', 1);

// load dw
require_once(WIKI_INC.'inc/init.php');

// load the parser so $PARSER_MODES is defined before the tests start
// otherwise PHPUnit unsets $PARSER_MODES in some cases which breaks p_get_parsermodes()
require_once(WIKI_INC.'inc/parser/parser.php');

