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
    $ns = getNS($id);
    if($ns){
      $name = shorten(noNS($id), ' ('.$ns.')',30);
    }else{
      $name = $id;
    }
    print html_wikilink(':'.$id,$name);
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
  global $NS;
  require_once(DOKU_INC.'inc/media.php');
  require_once(DOKU_INC.'inc/template.php');

  $NS = $_POST['ns'];
  tpl_mediaContent(true);
}

/**
 * Return list of search result for the Mediamanager
 *
 * @author Tobias Sarnowski <sarnowski@cosmocode.de>
 */
function ajax_mediasearchlist(){
  global $conf;
  require_once(DOKU_INC.'inc/media.php');

  media_searchlist($_POST['ns']);
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

/**
 * List matching namespaces and pages for the link wizard
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 */
function ajax_linkwiz(){
  global $conf;
  global $lang;
  require_once(DOKU_INC.'inc/html.php');

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
    require_once(DOKU_INC.'inc/fulltext.php');
    require_once(DOKU_INC.'inc/parserutils.php');
    $pages = array();
    $pages = ft_pageLookup($id,false);

    // result contains matches in pages and namespaces
    // we now extract the matching namespaces to show
    // them seperately
    $dirs  = array();
    $count = count($pages);
    for($i=0; $i<$count; $i++){
      if(strpos(noNS($pages[$i]),$id) === false){
        // match was in the namespace
        $dirs[getNS($pages[$i])] = 1; // assoc array avoids dupes
      }else{
        // it is a matching page, add it to the result
        $data[] = array(
          'id'    => $pages[$i],
          'title' => p_get_first_heading($pages[$i],false),
          'type'  => 'f',
        );
      }
      unset($pages[$i]);
    }
    foreach($dirs as $dir => $junk){
      $data[] = array(
        'id'   => $dir,
        'type' => 'd',
      );
    }

  }else{

    require_once(DOKU_INC.'inc/search.php');
    $opts = array(
      'depth' => 1,
      'listfiles' => true,
      'listdirs'  => true,
      'pagesonly' => true,
      'firsthead' => true,
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
