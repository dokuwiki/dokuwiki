<?php
/**
 * XML feed export
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

  if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__)).'/');
  require_once(DOKU_INC.'inc/init.php');
  require_once(DOKU_INC.'inc/common.php');
  require_once(DOKU_INC.'inc/parserutils.php');
  require_once(DOKU_INC.'inc/feedcreator.class.php');
  require_once(DOKU_INC.'inc/auth.php');

  //close session
  session_write_close();


  $num   = $_REQUEST['num'];
  $type  = $_REQUEST['type'];
  $mode  = $_REQUEST['mode'];
  $ns    = $_REQUEST['ns'];
  $ltype = $_REQUEST['linkto'];

  switch ($type){
    case 'rss':
       $type = 'RSS0.9';
       break;
    case 'rss2':
       $type = 'RSS2.0';
       break;
    case 'atom':
       $type = 'ATOM0.3';
       break;
    default:
       $type = 'RSS1.0';
  }

	// the feed is dynamic - we need a cache for each combo
  // (but most people just use the default feed so it's still effective)
	$cache = getCacheName($num.$type.$mode.$ns.$ltype.$_SERVER['REMOTE_USER'],'.feed');

	// check cacheage and deliver if nothing has changed since last
  // time (with 5 minutes settletime)
	$cmod = @filemtime($cache); // 0 if not exists
	if($cmod && ($cmod+(5*60) >= @filemtime($conf['changelog']))){
  	header('Content-Type: application/xml; charset=utf-8');
		print io_readFile($cache);
		exit;
  }

	// create new feed
  $rss = new DokuWikiFeedCreator();
  $rss->title = $conf['title'].(($ns) ? ' '.$ns : '');
  $rss->link  = DOKU_URL;
  $rss->syndicationURL = DOKU_URL.'feed.php';
  $rss->cssStyleSheet  = DOKU_URL.'lib/styles/feed.css';

  $image = new FeedImage();
  $image->title = $conf['title'];
  $image->url = DOKU_URL."lib/images/favicon.ico";
  $image->link = DOKU_URL;
  $rss->image = $image;

  if($mode == 'list'){
    rssListNamespace($rss,$ns);
  }else{
    rssRecentChanges($rss,$num,$ltype,$ns);
  }

  $feed = $rss->createFeed($type,'utf-8');
	
  // save cachefile
	io_saveFile($cache,$feed);

	// finally deliver
  header('Content-Type: application/xml; charset=utf-8');
  print $feed;

// ---------------------------------------------------------------- //

/**
 * Add recent changed to a feed object
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function rssRecentChanges(&$rss,$num,$ltype,$ns){
  global $conf;
  if(!$num) $num = $conf['recent'];

  $recents = getRecents(0,$num,false,$ns);

  //this can take some time if a lot of recaching has to be done
  @set_time_limit(90); // set max execution time

  foreach(array_keys($recents) as $id){

    $item = new FeedItem();
    $item->title = $id;
    $xhtml = p_wiki_xhtml($id,'',false);
    
    if($conf['useheading']) {
        $matches = array();
        if(preg_match('|<h([1-9])>(.*?)</h\1>|', $xhtml, $matches))
            $item->title = trim($matches[2]);
    }
    if(!empty($recents[$id]['sum'])){
      $item->title .= ' - '.strip_tags($recents[$id]['sum']);
    }

    $desc = cleanDesc($xhtml);

    if(empty($ltype))
      $ltype = $conf['rss_linkto'];

    switch ($ltype){
      case 'page':
        $item->link = wl($id,'rev='.$recents[$id]['date'],true);
        break;
      case 'rev':
        $item->link = wl($id,'do=revisions&amp;rev='.$recents[$id]['date'],true);
        break;
      case 'current':
        $item->link = wl($id, '', true);
        break;
      case 'diff':
      default:
        $item->link = wl($id,'do=diff&amp;'.$recents[$id]['date'],true);
    }

    $item->description = $desc;
    $item->date        = date('r',$recents[$id]['date']);
    if(strpos($id,':')!==false){
      $item->category    = substr($id,0,strrpos($id,':'));
    }
    if($recents[$id]['user']){
      $item->author = $recents[$id]['user'].'@';
    }else{
      $item->author = 'anonymous@';
    }
    $item->author  .= $recents[$id]['ip'];
    $rss->addItem($item);
  }
}

/**
 * Add all pages of a namespace to a feedobject
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function rssListNamespace(&$rss,$ns){
  require_once(DOKU_INC.'inc/search.php');
  global $conf;

  $ns=':'.cleanID($ns);
  $ns=str_replace(':','/',$ns);

  $data = array();
  sort($data);
  search($data,$conf['datadir'],'search_list','',$ns);
  foreach($data as $row){
    $id = $row['id'];
    $date = filemtime(wikiFN($id));
    $desc = cleanDesc(p_wiki_xhtml($id,'',false));
    $item = new FeedItem();
    $item->title       = $id;
    $item->link        = wl($id,'rev='.$date,true);
    $item->description = $desc;
    $item->date        = date('r',$date);
    $rss->addItem($item);
  }  
}

/**
 * Clean description for feed inclusion
 *
 * Removes HTML tags and line breaks and trims the text to
 * 250 chars
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function cleanDesc($desc){
  //start description at text of first paragraph
  $matches = array();
  if(preg_match('/<p>|<p\s.*?>/', $desc, $matches, PREG_OFFSET_CAPTURE))
      $desc = substr($desc, $matches[0][1]);

  //remove TOC
  $desc = preg_replace('!<div class="toc">.*?(</div>\n</div>)!s','',$desc);
  $desc = strip_tags($desc);
  $desc = preg_replace('/[\n\r\t]/',' ',$desc);
  $desc = preg_replace('/  /',' ',$desc);
  $desc = utf8_substr($desc,0,250);
  $desc = $desc.'...';
  return $desc;
}

?>
