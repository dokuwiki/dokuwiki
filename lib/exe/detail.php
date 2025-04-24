<?php

use dokuwiki\Extension\Event;

if (!defined('DOKU_INC')) define('DOKU_INC', __DIR__ . '/../../');
if (!defined('DOKU_MEDIADETAIL')) define('DOKU_MEDIADETAIL', 1);

// define all DokuWiki globals here (needed within test requests but also helps to keep track)
global $INPUT, $INFO, $IMG, $ID, $REV, $SRC, $ERROR, $AUTH;

require_once(DOKU_INC . 'inc/init.php');

$IMG = getID('media');
$ID = cleanID($INPUT->str('id'));
$REV = $INPUT->int('rev');

// this makes some general info available as well as the info about the
// "parent" page
$INFO = array_merge(pageinfo(), mediainfo());

$tmp = [];
Event::createAndTrigger('DETAIL_STARTED', $tmp);

//close session
session_write_close();

$ERROR = false;
// check image permissions
$AUTH = auth_quickaclcheck($IMG);
if ($AUTH >= AUTH_READ) {
    // check if image exists
    $SRC = mediaFN($IMG, $REV);
    if (!file_exists($SRC)) {
        //doesn't exist!
        http_status(404);
        $ERROR = 'File not found';
    }
} else {
    // no auth
    $ERROR = p_locale_xhtml('denied');
}

//start output and load template
header('Content-Type: text/html; charset=utf-8');
include(template('detail.php'));
tpl_img_close();
