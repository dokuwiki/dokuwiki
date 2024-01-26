<?php

/**
 * Initialize some defaults needed for DokuWiki
 */

use dokuwiki\Extension\PluginController;
use dokuwiki\ErrorHandler;
use dokuwiki\Input\Input;
use dokuwiki\Extension\Event;
use dokuwiki\Extension\EventHandler;

/**
 * timing Dokuwiki execution
 *
 * @param integer $start
 *
 * @return mixed
 */
function delta_time($start = 0)
{
    return microtime(true) - ((float)$start);
}
define('DOKU_START_TIME', delta_time());

global $config_cascade;
$config_cascade = [];

// if available load a preload config file
$preload = fullpath(__DIR__) . '/preload.php';
if (file_exists($preload)) include($preload);

// define the include path
if (!defined('DOKU_INC')) define('DOKU_INC', fullpath(__DIR__ . '/../') . '/');

// define Plugin dir
if (!defined('DOKU_PLUGIN'))  define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

// define config path (packagers may want to change this to /etc/dokuwiki/)
if (!defined('DOKU_CONF')) define('DOKU_CONF', DOKU_INC . 'conf/');

// check for error reporting override or set error reporting to sane values
if (!defined('DOKU_E_LEVEL') && file_exists(DOKU_CONF . 'report_e_all')) {
    define('DOKU_E_LEVEL', E_ALL);
}
if (!defined('DOKU_E_LEVEL')) {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
} else {
    error_reporting(DOKU_E_LEVEL);
}

// avoid caching issues #1594
header('Vary: Cookie');

// init memory caches
global $cache_revinfo;
       $cache_revinfo = [];
global $cache_wikifn;
       $cache_wikifn = [];
global $cache_cleanid;
       $cache_cleanid = [];
global $cache_authname;
       $cache_authname = [];
global $cache_metadata;
       $cache_metadata = [];

// always include 'inc/config_cascade.php'
// previously in preload.php set fields of $config_cascade will be merged with the defaults
include(DOKU_INC . 'inc/config_cascade.php');

//prepare config array()
global $conf;
$conf = [];

// load the global config file(s)
foreach (['default', 'local', 'protected'] as $config_group) {
    if (empty($config_cascade['main'][$config_group])) continue;
    foreach ($config_cascade['main'][$config_group] as $config_file) {
        if (file_exists($config_file)) {
            include($config_file);
        }
    }
}

//prepare license array()
global $license;
$license = [];

// load the license file(s)
foreach (['default', 'local'] as $config_group) {
    if (empty($config_cascade['license'][$config_group])) continue;
    foreach ($config_cascade['license'][$config_group] as $config_file) {
        if (file_exists($config_file)) {
            include($config_file);
        }
    }
}

// set timezone (as in pre 5.3.0 days)
date_default_timezone_set(@date_default_timezone_get());

// define baseURL
if (!defined('DOKU_REL')) define('DOKU_REL', getBaseURL(false));
if (!defined('DOKU_URL')) define('DOKU_URL', getBaseURL(true));
if (!defined('DOKU_BASE')) {
    if ($conf['canonical']) {
        define('DOKU_BASE', DOKU_URL);
    } else {
        define('DOKU_BASE', DOKU_REL);
    }
}

// define whitespace
if (!defined('NL')) define('NL', "\n");
if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");

// define cookie and session id, append server port when securecookie is configured FS#1664
if (!defined('DOKU_COOKIE')) {
    $serverPort = $_SERVER['SERVER_PORT'] ?? '';
    define('DOKU_COOKIE', 'DW' . md5(DOKU_REL . (($conf['securecookie']) ? $serverPort : '')));
    unset($serverPort);
}

// define main script
if (!defined('DOKU_SCRIPT')) define('DOKU_SCRIPT', 'doku.php');

if (!defined('DOKU_TPL')) {
    /**
     * @deprecated 2012-10-13 replaced by more dynamic method
     * @see tpl_basedir()
     */
    define('DOKU_TPL', DOKU_BASE . 'lib/tpl/' . $conf['template'] . '/');
}

if (!defined('DOKU_TPLINC')) {
    /**
     * @deprecated 2012-10-13 replaced by more dynamic method
     * @see tpl_incdir()
     */
    define('DOKU_TPLINC', DOKU_INC . 'lib/tpl/' . $conf['template'] . '/');
}

// make session rewrites XHTML compliant
@ini_set('arg_separator.output', '&amp;');

// make sure global zlib does not interfere FS#1132
@ini_set('zlib.output_compression', 'off');

// increase PCRE backtrack limit
@ini_set('pcre.backtrack_limit', '20971520');

// enable gzip compression if supported
$httpAcceptEncoding = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';
$conf['gzip_output'] &= (strpos($httpAcceptEncoding, 'gzip') !== false);
global $ACT;
if (
    $conf['gzip_output'] &&
        !defined('DOKU_DISABLE_GZIP_OUTPUT') &&
        function_exists('ob_gzhandler') &&
        // Disable compression when a (compressed) sitemap might be delivered
        // See https://bugs.dokuwiki.org/index.php?do=details&task_id=2576
        $ACT != 'sitemap'
) {
    ob_start('ob_gzhandler');
}

// init session
if (!headers_sent() && !defined('NOSESSION')) {
    if (!defined('DOKU_SESSION_NAME'))     define('DOKU_SESSION_NAME', "DokuWiki");
    if (!defined('DOKU_SESSION_LIFETIME')) define('DOKU_SESSION_LIFETIME', 0);
    if (!defined('DOKU_SESSION_PATH')) {
        $cookieDir = empty($conf['cookiedir']) ? DOKU_REL : $conf['cookiedir'];
        define('DOKU_SESSION_PATH', $cookieDir);
    }
    if (!defined('DOKU_SESSION_DOMAIN'))   define('DOKU_SESSION_DOMAIN', '');

    // start the session
    init_session();

    // load left over messages
    if (isset($_SESSION[DOKU_COOKIE]['msg'])) {
        $MSG = $_SESSION[DOKU_COOKIE]['msg'];
        unset($_SESSION[DOKU_COOKIE]['msg']);
    }
}

// don't let cookies ever interfere with request vars
$_REQUEST = array_merge($_GET, $_POST);

// we don't want a purge URL to be digged
if (isset($_REQUEST['purge']) && !empty($_SERVER['HTTP_REFERER'])) unset($_REQUEST['purge']);

// precalculate file creation modes
init_creationmodes();

// make real paths and check them
init_paths();
init_files();

// setup plugin controller class (can be overwritten in preload.php)
global $plugin_controller_class, $plugin_controller;
if (empty($plugin_controller_class)) $plugin_controller_class = PluginController::class;

// autoloader
require_once(DOKU_INC . 'inc/load.php');

// from now on everything is an exception
ErrorHandler::register();

// disable gzip if not available
define('DOKU_HAS_BZIP', function_exists('bzopen'));
define('DOKU_HAS_GZIP', function_exists('gzopen'));
if ($conf['compression'] == 'bz2' && !DOKU_HAS_BZIP) {
    $conf['compression'] = 'gz';
}
if ($conf['compression'] == 'gz' && !DOKU_HAS_GZIP) {
    $conf['compression'] = 0;
}

// input handle class
global $INPUT;
$INPUT = new Input();

// initialize plugin controller
$plugin_controller = new $plugin_controller_class();

// initialize the event handler
global $EVENT_HANDLER;
$EVENT_HANDLER = new EventHandler();

$local = $conf['lang'];
Event::createAndTrigger('INIT_LANG_LOAD', $local, 'init_lang', true);


// setup authentication system
if (!defined('NOSESSION')) {
    auth_setup();
}

// setup mail system
mail_setup();

$nil = null;
Event::createAndTrigger('DOKUWIKI_INIT_DONE', $nil, null, false);

/**
 * Initializes the session
 *
 * Makes sure the passed session cookie is valid, invalid ones are ignored an a new session ID is issued
 *
 * @link http://stackoverflow.com/a/33024310/172068
 * @link http://php.net/manual/en/session.configuration.php#ini.session.sid-length
 */
function init_session()
{
    global $conf;
    session_name(DOKU_SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => DOKU_SESSION_LIFETIME,
        'path' => DOKU_SESSION_PATH,
        'domain' => DOKU_SESSION_DOMAIN,
        'secure' => ($conf['securecookie'] && is_ssl()),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    // make sure the session cookie contains a valid session ID
    if (isset($_COOKIE[DOKU_SESSION_NAME]) && !preg_match('/^[-,a-zA-Z0-9]{22,256}$/', $_COOKIE[DOKU_SESSION_NAME])) {
        unset($_COOKIE[DOKU_SESSION_NAME]);
    }

    session_start();
}


/**
 * Checks paths from config file
 */
function init_paths()
{
    global $conf;

    $paths = [
        'datadir'   => 'pages',
        'olddir'    => 'attic',
        'mediadir'  => 'media',
        'mediaolddir' => 'media_attic',
        'metadir'   => 'meta',
        'mediametadir' => 'media_meta',
        'cachedir'  => 'cache',
        'indexdir'  => 'index',
        'lockdir'   => 'locks',
        'tmpdir'    => 'tmp',
        'logdir'    => 'log',
    ];

    foreach ($paths as $c => $p) {
        $path = empty($conf[$c]) ? $conf['savedir'] . '/' . $p : $conf[$c];
        $conf[$c] = init_path($path);
        if (empty($conf[$c])) {
            $path = fullpath($path);
            nice_die("The $c ('$p') at $path is not found, isn't accessible or writable.
                You should check your config and permission settings.
                Or maybe you want to <a href=\"install.php\">run the
                installer</a>?");
        }
    }

    // path to old changelog only needed for upgrading
    $conf['changelog_old'] = init_path(
        $conf['changelog'] ?? $conf['savedir'] . '/changes.log'
    );
    if ($conf['changelog_old'] == '') {
        unset($conf['changelog_old']);
    }
    // hardcoded changelog because it is now a cache that lives in meta
    $conf['changelog'] = $conf['metadir'] . '/_dokuwiki.changes';
    $conf['media_changelog'] = $conf['metadir'] . '/_media.changes';
}

/**
 * Load the language strings
 *
 * @param string $langCode language code, as passed by event handler
 */
function init_lang($langCode)
{
    //prepare language array
    global $lang, $config_cascade;
    $lang = [];

    //load the language files
    require(DOKU_INC . 'inc/lang/en/lang.php');
    foreach ($config_cascade['lang']['core'] as $config_file) {
        if (file_exists($config_file . 'en/lang.php')) {
            include($config_file . 'en/lang.php');
        }
    }

    if ($langCode && $langCode != 'en') {
        if (file_exists(DOKU_INC . "inc/lang/$langCode/lang.php")) {
            require(DOKU_INC . "inc/lang/$langCode/lang.php");
        }
        foreach ($config_cascade['lang']['core'] as $config_file) {
            if (file_exists($config_file . "$langCode/lang.php")) {
                include($config_file . "$langCode/lang.php");
            }
        }
    }
}

/**
 * Checks the existence of certain files and creates them if missing.
 */
function init_files()
{
    global $conf;

    $files = [$conf['indexdir'] . '/page.idx'];

    foreach ($files as $file) {
        if (!file_exists($file)) {
            $fh = @fopen($file, 'a');
            if ($fh) {
                fclose($fh);
                if ($conf['fperm']) chmod($file, $conf['fperm']);
            } else {
                nice_die("$file is not writable. Check your permissions settings!");
            }
        }
    }
}

/**
 * Returns absolute path
 *
 * This tries the given path first, then checks in DOKU_INC.
 * Check for accessibility on directories as well.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @param string $path
 *
 * @return bool|string
 */
function init_path($path)
{
    // check existence
    $p = fullpath($path);
    if (!file_exists($p)) {
        $p = fullpath(DOKU_INC . $path);
        if (!file_exists($p)) {
            return '';
        }
    }

    // check writability
    if (!@is_writable($p)) {
        return '';
    }

    // check accessability (execute bit) for directories
    if (@is_dir($p) && !file_exists("$p/.")) {
        return '';
    }

    return $p;
}

/**
 * Sets the internal config values fperm and dperm which, when set,
 * will be used to change the permission of a newly created dir or
 * file with chmod. Considers the influence of the system's umask
 * setting the values only if needed.
 */
function init_creationmodes()
{
    global $conf;

    // Legacy support for old umask/dmask scheme
    unset($conf['dmask']);
    unset($conf['fmask']);
    unset($conf['umask']);

    $conf['fperm'] = false;
    $conf['dperm'] = false;

    // get system umask, fallback to 0 if none available
    $umask = @umask();
    if (!$umask) $umask = 0000;

    // check what is set automatically by the system on file creation
    // and set the fperm param if it's not what we want
    $auto_fmode = 0666 & ~$umask;
    if ($auto_fmode != $conf['fmode']) $conf['fperm'] = $conf['fmode'];

    // check what is set automatically by the system on directory creation
    // and set the dperm param if it's not what we want.
    $auto_dmode = 0777 & ~$umask;
    if ($auto_dmode != $conf['dmode']) $conf['dperm'] = $conf['dmode'];
}

/**
 * Returns the full absolute URL to the directory where
 * DokuWiki is installed in (includes a trailing slash)
 *
 * !! Can not access $_SERVER values through $INPUT
 * !! here as this function is called before $INPUT is
 * !! initialized.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @param null|bool $abs Return an absolute URL? (null defaults to $conf['canonical'])
 *
 * @return string
 */
function getBaseURL($abs = null)
{
    global $conf;

    $abs ??= $conf['canonical'];

    if (!empty($conf['basedir'])) {
        $dir = $conf['basedir'];
    } elseif (substr($_SERVER['SCRIPT_NAME'], -4) == '.php') {
        $dir = dirname($_SERVER['SCRIPT_NAME']);
    } elseif (substr($_SERVER['PHP_SELF'], -4) == '.php') {
        $dir = dirname($_SERVER['PHP_SELF']);
    } elseif ($_SERVER['DOCUMENT_ROOT'] && $_SERVER['SCRIPT_FILENAME']) {
        $dir = preg_replace(
            '/^' . preg_quote($_SERVER['DOCUMENT_ROOT'], '/') . '/',
            '',
            $_SERVER['SCRIPT_FILENAME']
        );
        $dir = dirname('/' . $dir);
    } else {
        $dir = ''; //probably wrong, but we assume it's in the root
    }

    $dir = str_replace('\\', '/', $dir);             // bugfix for weird WIN behaviour
    $dir = preg_replace('#//+#', '/', "/$dir/");     // ensure leading and trailing slashes

    //handle script in lib/exe dir
    $dir = preg_replace('!lib/exe/$!', '', $dir);

    //handle script in lib/plugins dir
    $dir = preg_replace('!lib/plugins/.*$!', '', $dir);

    //finish here for relative URLs
    if (!$abs) return $dir;

    //use config if available, trim any slash from end of baseurl to avoid multiple consecutive slashes in the path
    if (!empty($conf['baseurl'])) return rtrim($conf['baseurl'], '/') . $dir;

    //split hostheader into host and port
    if (isset($_SERVER['HTTP_HOST'])) {
        if (
            (!empty($conf['trustedproxy'])) && isset($_SERVER['HTTP_X_FORWARDED_HOST'])
             && preg_match('/' . $conf['trustedproxy'] . '/', $_SERVER['REMOTE_ADDR'])
        ) {
            $cur_host = $_SERVER['HTTP_X_FORWARDED_HOST'];
        } else {
            $cur_host = $_SERVER['HTTP_HOST'];
        }
        $parsed_host = parse_url('http://' . $cur_host);
        $host = $parsed_host['host'] ?? '';
        $port = $parsed_host['port'] ?? '';
    } elseif (isset($_SERVER['SERVER_NAME'])) {
        $parsed_host = parse_url('http://' . $_SERVER['SERVER_NAME']);
        $host = $parsed_host['host'] ?? '';
        $port = $parsed_host['port'] ?? '';
    } else {
        $host = php_uname('n');
        $port = '';
    }

    if (!is_ssl()) {
        $proto = 'http://';
        if ($port == '80') {
            $port = '';
        }
    } else {
        $proto = 'https://';
        if ($port == '443') {
            $port = '';
        }
    }

    if ($port !== '') $port = ':' . $port;

    return $proto . $host . $port . $dir;
}

/**
 * Check if accessed via HTTPS
 *
 * Apache leaves ,$_SERVER['HTTPS'] empty when not available, IIS sets it to 'off'.
 * 'false' and 'disabled' are just guessing
 *
 * @returns bool true when SSL is active
 */
function is_ssl()
{
    global $conf;

    // check if we are behind a reverse proxy
    if (
        (!empty($conf['trustedproxy'])) && isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
         && preg_match('/' . $conf['trustedproxy'] . '/', $_SERVER['REMOTE_ADDR'])
         && ($_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
    ) {
        return true;
    }

    if (preg_match('/^(|off|false|disabled)$/i', $_SERVER['HTTPS'] ?? 'off')) {
        return false;
    }

    return true;
}

/**
 * checks it is windows OS
 * @return bool
 */
function isWindows()
{
    return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
}

/**
 * print a nice message even if no styles are loaded yet.
 *
 * @param integer|string $msg
 */
function nice_die($msg)
{
    echo<<<EOT
<!DOCTYPE html>
<html>
<head><title>DokuWiki Setup Error</title></head>
<body style="font-family: Arial, sans-serif">
    <div style="width:60%; margin: auto; background-color: #fcc;
                border: 1px solid #faa; padding: 0.5em 1em;">
        <h1 style="font-size: 120%">DokuWiki Setup Error</h1>
        <p>$msg</p>
    </div>
</body>
</html>
EOT;
    if (defined('DOKU_UNITTEST')) {
        throw new RuntimeException('nice_die: ' . $msg);
    }
    exit(1);
}

/**
 * A realpath() replacement
 *
 * This function behaves similar to PHP's realpath() but does not resolve
 * symlinks or accesses upper directories
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author <richpageau at yahoo dot co dot uk>
 * @link   http://php.net/manual/en/function.realpath.php#75992
 *
 * @param string $path
 * @param bool $exists
 *
 * @return bool|string
 */
function fullpath($path, $exists = false)
{
    static $run = 0;
    $root  = '';
    $iswin = (isWindows() || !empty($GLOBALS['DOKU_UNITTEST_ASSUME_WINDOWS']));

    // find the (indestructable) root of the path - keeps windows stuff intact
    if ($path[0] == '/') {
        $root = '/';
    } elseif ($iswin) {
        // match drive letter and UNC paths
        if (preg_match('!^([a-zA-z]:)(.*)!', $path, $match)) {
            $root = $match[1] . '/';
            $path = $match[2];
        } elseif (preg_match('!^(\\\\\\\\[^\\\\/]+\\\\[^\\\\/]+[\\\\/])(.*)!', $path, $match)) {
            $root = $match[1];
            $path = $match[2];
        }
    }
    $path = str_replace('\\', '/', $path);

    // if the given path wasn't absolute already, prepend the script path and retry
    if (!$root) {
        $base = dirname($_SERVER['SCRIPT_FILENAME']);
        $path = $base . '/' . $path;
        if ($run == 0) { // avoid endless recursion when base isn't absolute for some reason
            $run++;
            return fullpath($path, $exists);
        }
    }
    $run = 0;

    // canonicalize
    $path = explode('/', $path);
    $newpath = [];
    foreach ($path as $p) {
        if ($p === '' || $p === '.') continue;
        if ($p === '..') {
            array_pop($newpath);
            continue;
        }
        $newpath[] = $p;
    }
    $finalpath = $root . implode('/', $newpath);

    // check for existence when needed (except when unit testing)
    if ($exists && !defined('DOKU_UNITTEST') && !file_exists($finalpath)) {
        return false;
    }
    return $finalpath;
}
