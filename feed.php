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

  //set auth header for login
  if($_REQUEST['login'] && !isset($_SERVER['PHP_AUTH_USER'])){
    header('WWW-Authenticate: Basic realm="'.$conf['title'].'"');
    header('HTTP/1.0 401 Unauthorized');
    auth_logoff();
  }


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

  //some defaults for the feed
  $CACHEGROUP = 'feed';
  $conf['typography'] = false;
  $conf['canonical']  = true;
  $parser['toc']      = false;

#  $rss = new UniversalFeedCreator();
  $rss = new DokuWikiFeedCreator();
  $rss->title = $conf['title'];
  $rss->link  = DOKU_URL;
  $rss->syndicationURL = DOKU_URL.'/feed.php';
  $rss->cssStyleSheet  = DOKU_URL.'/feed.css';

  $image = new FeedImage();
  $image->title = $conf['title'];
  $image->url = DOKU_URL."images/favicon.ico";
  $image->link = DOKU_URL;
  $rss->image = $image;

  if($mode == 'list'){
    rssListNamespace($rss,$ns);
  }else{
    rssRecentChanges($rss,$num,$ltype);
  }

  header('Content-Type: application/xml; charset=utf-8');
  print $rss->createFeed($type,'utf-8');

// ---------------------------------------------------------------- //

/**
 * Add recent changed to a feed object
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function rssRecentChanges(&$rss,$num,$ltype){
  $recents = getRecents($num);
  foreach(array_keys($recents) as $id){
    $desc = cleanDesc(p_wiki_xhtml($id,'',false));
    $item = new FeedItem();
    $item->title       = $id;
    if(!empty($recents[$id]['sum'])){
      $item->title .= ' - '.strip_tags($recents[$id]['sum']);
    }
		
		switch ($ltype){
			case 'page':
    		$item->link = wl($id,'rev='.$recents[$id]['date'],true);
				break;
			case 'rev':
				$item->link = wl($id,'do=revisions&amp;rev='.$recents[$id]['date'],true);
				break;
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

    //this can take some time if a lot of recaching has to be done
    @set_time_limit(30); //reset execution time
  }
}

/**
 * Add all pages of a namespace to a feedobject
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function rssListNamespace(&$rss,$ns){
  require_once("inc/search.php");
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
  //remove TOC
  $desc = preg_replace('!<div class="toc">.*?(</div>\n</div>)!s','',$desc);
  $desc = strip_tags($desc);
  $desc = preg_replace('/[\n\r\t]/',' ',$desc);
  $desc = preg_replace('/  /',' ',$desc);
  $desc = substr($desc,0,250);
  $desc = $desc.'...';
  return $desc;
}

?>
