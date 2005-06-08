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

  $nsdir = str_replace(':','/',getNS($query));
  require_once(DOKU_INC.'inc/search.php');
  require_once(DOKU_INC.'inc/html.php');

  $data = array();
  search($data,$conf['datadir'],'search_qsearch',array(query => $query),$nsdir);

  if(!count($data)) return;

  print '<b>'.$lang['quickhits'].'</b>';
  print html_buildlist($data,'qsearch','html_list_index');
}

//Setup VIM: ex: et ts=2 enc=utf-8 :
?>
