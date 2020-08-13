<?php
/**
 * DokuWiki AJAX call handler
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) define('DOKU_INC', dirname(__FILE__) . '/../../');
require_once(DOKU_INC . 'inc/init.php');

//close session
session_write_close();

// default header, ajax call may overwrite it later
header('Content-Type: text/html; charset=utf-8');

//call the requested function
global $INPUT;
if($INPUT->has('call')) {
    $call = $INPUT->filter('utf8_stripspecials')->str('call');
    new \dokuwiki\Ajax($call);
} else {
    http_status(404);
}
