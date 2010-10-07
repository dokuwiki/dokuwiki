<?php
if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/../../');
define('DOKU_MEDIADETAIL',1);
require_once(DOKU_INC.'inc/init.php');
//close session
session_write_close();

$IMG  = getID('media');
$ID   = cleanID($_REQUEST['id']);

if($conf['allowdebug'] && $_REQUEST['debug']){
    print '<pre>';
    foreach(explode(' ','basedir userewrite baseurl useslash') as $x){
        print '$'."conf['$x'] = '".$conf[$x]."';\n";
    }
    foreach(explode(' ','DOCUMENT_ROOT HTTP_HOST SCRIPT_FILENAME PHP_SELF '.
                'REQUEST_URI SCRIPT_NAME PATH_INFO PATH_TRANSLATED') as $x){
        print '$'."_SERVER['$x'] = '".$_SERVER[$x]."';\n";
    }
    print "getID('media'): ".getID('media')."\n";
    print "getID('media',false): ".getID('media',false)."\n";
    print '</pre>';
}

$ERROR = false;
// check image permissions
$AUTH = auth_quickaclcheck($IMG);
if($AUTH >= AUTH_READ){
    // check if image exists
    $SRC = mediaFN($IMG);
    if(!@file_exists($SRC)){
        //doesn't exist!
        header("HTTP/1.0 404 File not Found");
        $ERROR = 'File not found';
    }
}else{
    // no auth
    $ERROR = p_locale_xhtml('denied');
}

// this makes some general infos available as well as the info about the
// "parent" page
$INFO = pageinfo();

//start output and load template
header('Content-Type: text/html; charset=utf-8');
include(template('detail.php'));

