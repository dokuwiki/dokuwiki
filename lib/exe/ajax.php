<?php
/**
 * DokuWiki AJAX call handler
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/../../');
require_once(DOKU_INC.'inc/init.php');
require_once(DOKU_INC.'inc/ajax_functions.php');

//close session
session_write_close();

header('Content-Type: text/html; charset=utf-8');

//call the requested function
if($INPUT->has('call')) {
    $call = $INPUT->filter('utf8_stripspecials')->str('call');
    $callfn = 'ajax_'.$call;

    if(function_exists($callfn)) {
        $callfn();
    } else {
        $evt = new Doku_Event('AJAX_CALL_UNKNOWN', $call);
        if ($evt->advise_before()) {
            print "AJAX call '".htmlspecialchars($call)."' unknown!\n";
        } else {
            $evt->advise_after();
            unset($evt);
        }
    }
}