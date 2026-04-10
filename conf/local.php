<?php

$conf['title'] = 'Uprzejme Wiki';
$conf['lang'] = 'pl';
$conf['license'] = 'cc-by-sa';
$conf['disableactions'] = 'register';
$conf['template']    = 'uprzejmiedonosze';


$conf['baseurl']      = 'https://uprzejmiedonosze.net/';
$conf['basedir']      = '/wiki/';
$conf['cookiedir']    = '/wiki/';
$conf['defaultgroup'] = 'user';
$conf['sepchar']      = '_';
$conf['breadcrumbs']  = 0;
$conf['useacl']       = 1;
$conf['userewrite']   = 1;
$conf['useslash']     = 1;
$conf['useheading']   = 1;
$conf['defer_js']     = 0;

if ($_SERVER['HTTP_HOST'] === 'ud-dev.x93.org') {
    $conf['baseurl']    = 'https://ud-dev.x93.org/';
} else if ($_SERVER['HTTP_HOST'] === 'localhost:8080') {
    $conf['baseurl']    = 'http://localhost:8080/';
    $conf['basedir']    = '';
    $conf['useacl']     = 0;
    $conf['userewrite'] = 0;
} else {
    require(dirname(__FILE__) . '/local-prod.php');
}

$conf['dontlog'] = '';
