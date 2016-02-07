<?php
/**
 * DokuWiki AJAX call handler
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/../../');
require_once(DOKU_INC.'inc/init.php');
//close session
session_write_close();

if(!function_exists('ajax_qsearch')) {
	require_once 'ajax_functions.php';
}

//call the requested function
if($INPUT->post->has('call')){
    $call = $INPUT->post->str('call');
}else if($INPUT->get->has('call')){
    $call = $INPUT->get->str('call');
}else{
    exit;
}
$callfn = 'ajax_'.$call;

if(function_exists($callfn)){
    $callfn();
}else{
    $evt = new Doku_Event('AJAX_CALL_UNKNOWN', $call);
    if ($evt->advise_before()) {
        print "AJAX call '".htmlspecialchars($call)."' unknown!\n";
        exit;
    }
    $evt->advise_after();
    unset($evt);
}

//Setup VIM: ex: et ts=2 :
