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
if(php_sapi_name() != 'cli-server') {
    header("Location: doku.php");
    exit;
}

# ROUTER starts below

# avoid path traversal
$_SERVER['SCRIPT_NAME'] = str_replace('/../', '/', $_SERVER['SCRIPT_NAME']);

# routing aka. rewriting
if(preg_match('/^\/_media\/(.*)/', $_SERVER['SCRIPT_NAME'], $m)) {
    # media dispatcher
    $_GET['media'] = $m[1];
    require $_SERVER['DOCUMENT_ROOT'] . '/lib/exe/fetch.php';

} else if(preg_match('/^\/_detail\/(.*)/', $_SERVER['SCRIPT_NAME'], $m)) {
    # image detail view
    $_GET['media'] = $m[1];
    require $_SERVER['DOCUMENT_ROOT'] . '/lib/exe/detail.php';

} else if(preg_match('/^\/_media\/(.*)/', $_SERVER['SCRIPT_NAME'], $m)) {
    # exports
    $_GET['do'] = 'export_' . $m[1];
    $_GET['id'] = $m[2];
    require $_SERVER['DOCUMENT_ROOT'] . '/doku.php';

} elseif($_SERVER['SCRIPT_NAME'] == '/index.php') {
    # 404s are automatically mapped to index.php
    if(isset($_SERVER['PATH_INFO'])) {
        $_GET['id'] = $_SERVER['PATH_INFO'];
    }
    require $_SERVER['DOCUMENT_ROOT'] . '/doku.php';

} else if(file_exists($_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME'])) {
    # existing files

    # access limitiations
    if(preg_match('/\/([\._]ht|README$|VERSION$|COPYING$)/', $_SERVER['SCRIPT_NAME']) or
        preg_match('/^\/(data|conf|bin|inc)\//', $_SERVER['SCRIPT_NAME'])
    ) {
        die('Access denied');
    }

    if(substr($_SERVER['SCRIPT_NAME'], -4) == '.php') {
        # php scripts
        require $_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME'];
    } else {
        # static files
        return false;
    }
}
# 404
