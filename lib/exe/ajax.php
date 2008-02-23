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

if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/../../');
require_once(DOKU_INC.'inc/init.php');
require_once(DOKU_INC.'inc/common.php');
require_once(DOKU_INC.'inc/pageutils.php');
require_once(DOKU_INC.'inc/auth.php');
//close sesseion
session_write_close();

header('Content-Type: text/html; charset=utf-8');


//call the requested function
if(isset($_POST['call']))
  $call = 'ajax_'.$_POST['call'];
else if(isset($_GET['call']))
  $call = 'ajax_'.$_GET['call'];
else
  exit;
if(function_exists($call)){
  $call();
}else{
  $call = $_POST['call'];
  $evt = new Doku_Event('AJAX_CALL_UNKNOWN', $call);
  if ($evt->advise_before()) {
    print "AJAX call '".htmlspecialchars($_POST['call'])."' unknown!\n";
  }
  $evt->advise_after();
  unset($evt);
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
  if(empty($query)) $query = cleanID($_GET['q']);
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
 * Support OpenSearch suggestions
 *
 * @link   http://www.opensearch.org/Specifications/OpenSearch/Extensions/Suggestions/1.0
 * @author Mike Frysinger <vapier@gentoo.org>
 */
function ajax_suggestions() {
  global $conf;
  global $lang;

  $query = cleanID($_POST['q']);
  if(empty($query)) $query = cleanID($_GET['q']);
  if(empty($query)) return;

  require_once(DOKU_INC.'inc/html.php');
  require_once(DOKU_INC.'inc/fulltext.php');
  require_once(DOKU_INC.'inc/JSON.php');

  $data = array();
  $data = ft_pageLookup($query);
  if(!count($data)) return;

  // limit results to 15 hits
  $data = array_slice($data, 0, 15);
  $data = array_map('trim',$data);
  $data = array_map('noNS',$data);
  $data = array_unique($data);
  sort($data);

  /* now construct a json */
  $suggestions = array(
    $query,  // the original query
    $data,   // some suggestions
    array(), // no description
    array()  // no urls
  );
  $json = new JSON();

  header('Content-Type: application/x-suggestions+json');
  print $json->encode($suggestions);
}

/**
 * Refresh a page lock and save draft
 *
 * Andreas Gohr <andi@splitbrain.org>
 */
function ajax_lock(){
  global $conf;
  global $lang;
  $id = cleanID($_POST['id']);
  if(empty($id)) return;

  if(!checklock($id)){
    lock($id);
    echo 1;
  }

  if($conf['usedraft'] && $_POST['wikitext']){
    $client = $_SERVER['REMOTE_USER'];
    if(!$client) $client = clientIP(true);

    $draft = array('id'     => $id,
                   'prefix' => $_POST['prefix'],
                   'text'   => $_POST['wikitext'],
                   'suffix' => $_POST['suffix'],
                   'date'   => $_POST['date'],
                   'client' => $client,
                  );
    $cname = getCacheName($draft['client'].$id,'.draft');
    if(io_saveFile($cname,serialize($draft))){
      echo $lang['draftdate'].' '.strftime($conf['dformat']);
    }
  }

}

/**
 * Delete a draft
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function ajax_draftdel(){
  $id = cleanID($_POST['id']);
  if(empty($id)) return;

  $client = $_SERVER['REMOTE_USER'];
  if(!$client) $client = clientIP(true);

  $cname = getCacheName($client.$id,'.draft');
  @unlink($cname);
}

/**
 * Return subnamespaces for the Mediamanager
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function ajax_medians(){
  global $conf;
  require_once(DOKU_INC.'inc/search.php');
  require_once(DOKU_INC.'inc/media.php');

  // wanted namespace
  $ns  = cleanID($_POST['ns']);
  $dir  = utf8_encodeFN(str_replace(':','/',$ns));

  $lvl = count(explode(':',$ns));

  $data = array();
  search($data,$conf['mediadir'],'search_index',array('nofiles' => true),$dir);
  foreach($data as $item){
    $item['level'] = $lvl+1;
    echo media_nstree_li($item);
    echo media_nstree_item($item);
    echo '</li>';
  }
}

/**
 * Return list of files for the Mediamanager
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function ajax_medialist(){
  global $conf;
  require_once(DOKU_INC.'inc/media.php');

  media_filelist($_POST['ns']);
}

/**
 * Return sub index for index view
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function ajax_index(){
  global $conf;
  require_once(DOKU_INC.'inc/search.php');
  require_once(DOKU_INC.'inc/html.php');

  // wanted namespace
  $ns  = cleanID($_POST['idx']);
  $dir  = utf8_encodeFN(str_replace(':','/',$ns));

  $lvl = count(explode(':',$ns));

  $data = array();
  search($data,$conf['datadir'],'search_index',array('ns' => $ns),$dir);
  foreach($data as $item){
    $item['level'] = $lvl+1;
    echo html_li_index($item);
    echo '<div class="li">';
    echo html_list_index($item);
    echo '</div>';
    echo '</li>';
  }
}

//Setup VIM: ex: et ts=2 enc=utf-8 :
?>
