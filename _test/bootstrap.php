<?php
/**
 * Test Suite bootstrapping for DokuWiki
 */

if(!defined('DOKU_UNITTEST')) define('DOKU_UNITTEST',dirname(__FILE__).'/');
require_once DOKU_UNITTEST.'core/phpQuery-onefile.php';
require_once DOKU_UNITTEST.'core/DokuWikiTest.php';
require_once DOKU_UNITTEST.'core/TestResponse.php';
require_once DOKU_UNITTEST.'core/TestRequest.php';
require_once DOKU_UNITTEST.'core/TestUtils.php';


// backward compatibility to old test suite
define('SIMPLE_TEST', true);

// basic behaviours
define('DOKU_E_LEVEL',E_ALL ^ E_NOTICE);
error_reporting(DOKU_E_LEVEL);
set_time_limit(0);
ini_set('memory_limit','2048M');

// prepare temporary directories
define('DOKU_INC', dirname(dirname(__FILE__)).'/');
define('TMP_DIR', sys_get_temp_dir().'/dwtests-'.microtime(true));
define('DOKU_CONF', TMP_DIR.'/conf/');
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

// populate default dirs
TestUtils::rcopy(TMP_DIR, DOKU_INC.'/conf');
TestUtils::rcopy(TMP_DIR, dirname(__FILE__).'/conf');
mkdir(DOKU_TMP_DATA);
foreach(array(
    'attic', 'cache', 'index', 'locks', 'media',
    'media_attic', 'media_meta', 'meta', 'pages', 'tmp') as $dir){
    mkdir(DOKU_TMP_DATA.'/'.$dir);
}

// disable all non-default plugins by default
$dh = dir(DOKU_INC.'lib/plugins/');
while (false !== ($entry = $dh->read())) {
    if ($entry == '.' || $entry == '..') {
        continue;
    }

    if (!is_dir(DOKU_INC.'lib/plugins/'.$entry)) {
        continue;
    }

    if (!in_array($entry, $default_plugins)) {
        // disable this plugin
        TestUtils::fappend(DOKU_CONF.'plugins.local.php', "\$plugins['$entry'] = 0;\n");
    }
}
$dh->close();

// load dw
require_once(DOKU_INC.'inc/init.php');

// load the parser so $PARSER_MODES is defined before the tests start
// otherwise PHPUnit unsets $PARSER_MODES in some cases which breaks p_get_parsermodes()
require_once(DOKU_INC.'inc/parser/parser.php');

