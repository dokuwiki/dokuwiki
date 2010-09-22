<?php
/**
 * DokuWiki AJAX call handler
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

//fix for Opera XMLHttpRequests
if(!count($_POST) && !empty($HTTP_RAW_POST_DATA)){
  parse_str($HTTP_RAW_POST_DATA, $_POST);
}

if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/../../');
require_once(DOKU_INC.'inc/init.php');
//close session
session_write_close();

header('Content-Type: text/html; charset=utf-8');


//call the requested function
if(isset($_POST['call']))
  $call = $_POST['call'];
else if(isset($_GET['call']))
  $call = $_GET['call'];
else
  exit;

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

/**
 * Searches for matching pagenames
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function ajax_qsearch(){
  global $conf;
  global $lang;

  $query = $_POST['q'];
  if(empty($query)) $query = $_GET['q'];
  if(empty($query)) return;

  $data = ft_pageLookup($query, true, useHeading('navigation'));

  if(!count($data)) return;

  print '<strong>'.$lang['quickhits'].'</strong>';
  print '<ul>';
  foreach($data as $id => $title){
    if (useHeading('navigation')) {
        $name = $title;
    } else {
        $ns = getNS($id);
        if($ns){
          $name = shorten(noNS($id), ' ('.$ns.')',30);
        }else{
          $name = $id;
        }
    }
    echo '<li>' . html_wikilink(':'.$id,$name) . '</li>';
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

  $data = array();
  $data = ft_pageLookup($query);
  if(!count($data)) return;
  $data = array_keys($data);

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
                   'prefix' => substr($_POST['prefix'], 0, -1),
                   'text'   => $_POST['wikitext'],
                   'suffix' => $_POST['suffix'],
                   'date'   => (int) $_POST['date'],
                   'client' => $client,
                  );
    $cname = getCacheName($draft['client'].$id,'.draft');
    if(io_saveFile($cname,serialize($draft))){
      echo $lang['draftdate'].' '.dformat();
    }
  }

}

/**
 * Delete a draft
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function ajax_draftdel(){
  $id = cleanID($_REQUEST['id']);
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
  global $NS;

  $NS = $_POST['ns'];
  tpl_mediaContent(true);
}

/**
 * Return sub index for index view
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function ajax_index(){
  global $conf;

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

/**
 * List matching namespaces and pages for the link wizard
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 */
function ajax_linkwiz(){
  global $conf;
  global $lang;

  $q  = ltrim($_POST['q'],':');
  $id = noNS($q);
  $ns = getNS($q);

  $ns = cleanID($ns);
  $id = cleanID($id);

  $nsd  = utf8_encodeFN(str_replace(':','/',$ns));
  $idd  = utf8_encodeFN(str_replace(':','/',$id));

  $data = array();
  if($q && !$ns){

    // use index to lookup matching pages
    $pages = array();
    $pages = ft_pageLookup($id,true);

    // result contains matches in pages and namespaces
    // we now extract the matching namespaces to show
    // them seperately
    $dirs  = array();


    foreach($pages as $pid => $title){
      if(strpos(noNS($pid),$id) === false){
        // match was in the namespace
        $dirs[getNS($pid)] = 1; // assoc array avoids dupes
      }else{
        // it is a matching page, add it to the result
        $data[] = array(
          'id'    => $pid,
          'title' => $title,
          'type'  => 'f',
        );
      }
      unset($pages[$pid]);
    }
    foreach($dirs as $dir => $junk){
      $data[] = array(
        'id'   => $dir,
        'type' => 'd',
      );
    }

  }else{

    $opts = array(
      'depth' => 1,
      'listfiles' => true,
      'listdirs'  => true,
      'pagesonly' => true,
      'firsthead' => true,
      'sneakyacl' => $conf['sneaky_index'],
    );
    if($id) $opts['filematch'] = '^.*\/'.$id;
    if($id) $opts['dirmatch']  = '^.*\/'.$id;
    search($data,$conf['datadir'],'search_universal',$opts,$nsd);

    // add back to upper
    if($ns){
        array_unshift($data,array(
            'id'   => getNS($ns),
            'type' => 'u',
        ));
    }
  }

  // fixme sort results in a useful way ?

  if(!count($data)){
    echo $lang['nothingfound'];
    exit;
  }

  // output the found data
  $even = 1;
  foreach($data as $item){
    $even *= -1; //zebra

    if(($item['type'] == 'd' || $item['type'] == 'u') && $item['id']) $item['id'] .= ':';
    $link = wl($item['id']);

    echo '<div class="'.(($even > 0)?'even':'odd').' type_'.$item['type'].'">';


    if($item['type'] == 'u'){
        $name = $lang['upperns'];
    }else{
        $name = htmlspecialchars($item['id']);
    }

    echo '<a href="'.$link.'" title="'.htmlspecialchars($item['id']).'" class="wikilink1">'.$name.'</a>';

    if($item['title']){
      echo '<span>'.htmlspecialchars($item['title']).'</span>';
    }
    echo '</div>';
  }

}

//Setup VIM: ex: et ts=2 enc=utf-8 :
