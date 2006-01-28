<?php
/**
 * DokuWiki AJAX call handler
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

//fix for Opera XMLHttpRequests
if(!count($_POST) && $HTTP_RAW_POST_DATA){
  parse_str($HTTP_RAW_POST_DATA, $_POST);
}

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
require_once(DOKU_INC.'inc/init.php');
require_once(DOKU_INC.'inc/common.php');
require_once(DOKU_INC.'inc/pageutils.php');
require_once(DOKU_INC.'inc/auth.php');
//close sesseion
session_write_close();

header('Content-Type: text/html; charset=utf-8');


//call the requested function
$call = 'ajax_'.$_POST['call'];
if(function_exists($call)){
  $call();
}else{
  print "The called function '".htmlspecialchars($call)."' does not exist!";
}

/**
 * Searches for matching pagenames
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function ajax_qsearch(){
  global $conf;
  global $lang;

  $query = cleanID($_POST['q']);
  if(empty($query)) return;

  require_once(DOKU_INC.'inc/html.php');
  require_once(DOKU_INC.'inc/fulltext.php');

  $data = array();
  $data = ft_pageLookup($query);

  if(!count($data)) return;

  print '<strong>'.$lang['quickhits'].'</strong>';
  print '<ul>';
  foreach($data as $id){
    print '<li>';
    print html_wikilink(':'.$id);
    print '</li>';
  }
  print '</ul>';
}

/**
 * Refresh a page lock
 *
 * Andreas Gohr <andi@splitbrain.org>
 */
function ajax_lock(){
  $id = cleanID($_POST['id']);
  if(empty($id)) return;

  if(!checklock($id)){
    lock($id);
    print 1;
  }
}

//Setup VIM: ex: et ts=2 enc=utf-8 :
?>
