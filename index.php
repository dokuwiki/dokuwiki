<?php
/**
 * Forwarder/Router to doku.php
 *
 * In normal usage, this script simply redirects to doku.php. However it can also be used as a routing
 * script with PHP's builtin webserver. It takes care of .htaccess compatible rewriting, directory/file
 * access permission checking and passing on static files.
 *
 * Usage example:
 *
 *   php -S localhost:8000 index.php
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */
if (php_sapi_name() != 'cli-server') {
    if (!defined('DOKU_INC')) define('DOKU_INC', dirname(__FILE__) . '/');
    require_once(DOKU_INC . 'inc/init.php');

    send_redirect(DOKU_URL . 'doku.php');
}

// ROUTER starts below

// avoid path traversal
$_SERVER['SCRIPT_NAME'] = str_replace('/../', '/', $_SERVER['SCRIPT_NAME']);

// routing aka. rewriting
if (preg_match('/^\/_media\/(.*)/', $_SERVER['SCRIPT_NAME'], $m)) {
    // media dispatcher
    $_GET['media'] = $m[1];
    require $_SERVER['DOCUMENT_ROOT'] . '/lib/exe/fetch.php';

} elseif (preg_match('/^\/_detail\/(.*)/', $_SERVER['SCRIPT_NAME'], $m)) {
    // image detail view
    $_GET['media'] = $m[1];
    require $_SERVER['DOCUMENT_ROOT'] . '/lib/exe/detail.php';

} elseif (preg_match('/^\/_export\/([^\/]+)\/(.*)/', $_SERVER['SCRIPT_NAME'], $m)) {
    // exports
    $_GET['do'] = 'export_' . $m[1];
    $_GET['id'] = $m[2];
    require $_SERVER['DOCUMENT_ROOT'] . '/doku.php';

} elseif (
    $_SERVER['SCRIPT_NAME'] !== '/index.php' &&
    file_exists($_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME'])
) {
    // existing files

    // access limitiations
    if (preg_match('/\/([._]ht|README$|VERSION$|COPYING$)/', $_SERVER['SCRIPT_NAME']) or
        preg_match('/^\/(data|conf|bin|inc)\//', $_SERVER['SCRIPT_NAME'])
    ) {
        header('HTTP/1.1 403 Forbidden');
        die('Access denied');
    }

    if (substr($_SERVER['SCRIPT_NAME'], -4) == '.php') {
        # php scripts
        require $_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME'];
    } else {
        # static files
        return false;
    }
} else {
    // treat everything else as a potential wiki page
    // working around https://bugs.php.net/bug.php?id=61286
    $request_path = preg_split('/\?/', $_SERVER['REQUEST_URI'], 2)[0];
    if (isset($_SERVER['PATH_INFO'])) {
        $_GET['id'] = $_SERVER['PATH_INFO'];
    } elseif ($request_path != '/' && $request_path != '/index.php') {
        $_GET['id'] = $_SERVER['SCRIPT_NAME'];
    }

    require $_SERVER['DOCUMENT_ROOT'] . '/doku.php';
}
