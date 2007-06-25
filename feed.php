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
  require_once(DOKU_INC.'inc/events.php');
  require_once(DOKU_INC.'inc/parserutils.php');
  require_once(DOKU_INC.'inc/feedcreator.class.php');
  require_once(DOKU_INC.'inc/auth.php');
  require_once(DOKU_INC.'inc/pageutils.php');

  //close session
  session_write_close();


  $num   = $_REQUEST['num'];
  $type  = $_REQUEST['type'];
  $mode  = $_REQUEST['mode'];
  $minor = $_REQUEST['minor'];
  $ns    = $_REQUEST['ns'];
  $ltype = $_REQUEST['linkto'];

  if($type == '')
    $type = $conf['rss_type'];

  switch ($type){
    case 'rss':
       $type = 'RSS0.91';
       $mime = 'text/xml';
       break;
    case 'rss2':
       $type = 'RSS2.0';
       $mime = 'text/xml';
       break;
    case 'atom':
       $type = 'ATOM0.3';
       $mime = 'application/xml';
       break;
    case 'atom1':
       $type = 'ATOM1.0';
       $mime = 'application/atom+xml';
       break;
    default:
       $type = 'RSS1.0';
       $mime = 'application/xml';
  }

  // the feed is dynamic - we need a cache for each combo
  // (but most people just use the default feed so it's still effective)
  $cache = getCacheName($num.$type.$mode.$ns.$ltype.$_SERVER['REMOTE_USER'],'.feed');
  $cmod = @filemtime($cache); // 0 if not exists
  if ($cmod && (@filemtime(DOKU_CONF.'local.php')>$cmod || @filemtime(DOKU_CONF.'dokuwiki.php')>$cmod)) {
    // ignore cache if feed prefs may have changed
    $cmod = 0;
  }

  // check cacheage and deliver if nothing has changed since last
  // time or the update interval has not passed, also handles conditional requests
  header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
  header('Pragma: public');
  header('Content-Type: application/xml; charset=utf-8');
  if($cmod && (($cmod+$conf['rss_update']>time()) || ($cmod>@filemtime($conf['changelog'])))){
    http_conditionalRequest($cmod);
    if($conf['allowdebug']) header("X-CacheUsed: $cache");
    print io_readFile($cache);
    exit;
  } else {
    http_conditionalRequest(time());
  }

  // create new feed
  $rss = new DokuWikiFeedCreator();
  $rss->title = $conf['title'].(($ns) ? ' '.$ns : '');
  $rss->link  = DOKU_URL;
  $rss->syndicationURL = DOKU_URL.'feed.php';
  $rss->cssStyleSheet  = DOKU_URL.'lib/exe/css.php?s=feed';

  $image = new FeedImage();
  $image->title = $conf['title'];
  $image->url = DOKU_URL."lib/images/favicon.ico";
  $image->link = DOKU_URL;
  $rss->image = $image;

  if($mode == 'list'){
    rssListNamespace($rss,$ns);
  }else{
    rssRecentChanges($rss,$num,$ltype,$ns,$minor);
  }

  $feed = $rss->createFeed($type,'utf-8');

  // save cachefile
  io_saveFile($cache,$feed);

  // finally deliver
  print $feed;

// ---------------------------------------------------------------- //

/**
 * Add recent changed pages to a feed object
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function rssRecentChanges(&$rss,$num,$ltype,$ns,$minor){
    global $conf;
    global $auth;

    if(!$num) $num = $conf['recent'];
    $guardmail = ($conf['mailguard'] != '' && $conf['mailguard'] != 'none');


    $flags = RECENTS_SKIP_DELETED;
    if(!$minor) $flags += RECENTS_SKIP_MINORS;

    $recents = getRecents(0,$num,$ns,$flags);

    foreach($recents as $recent){
        $item = new FeedItem();
        $meta = p_get_metadata($recent['id']);

        if($conf['useheading'] && $meta['title']){
            $item->title = $meta['title'];
        }else{
            $item->title = $recent['id'];
        }
        if($conf['rss_show_summary'] && !empty($recent['sum'])){
            $item->title .= ' - '.strip_tags($recent['sum']);
        }

        if(empty($ltype)) $ltype = $conf['rss_linkto'];

        switch ($ltype){
            case 'page':
                $item->link = wl($recent['id'],'rev='.$recent['date'],true,'&');
                break;
            case 'rev':
                $item->link = wl($recent['id'],'do=revisions&rev='.$recent['date'],true,'&');
                break;
            case 'current':
                $item->link = wl($recent['id'], '', true,'&');
                break;
            case 'diff':
            default:
                $item->link = wl($recent['id'],'rev='.$recent['date'].'&do=diff',true,'&');
        }

        $item->description = $meta['description']['abstract'];
        $item->date        = date('r',$recent['date']);
        $cat = getNS($recent['id']);
        if($cat) $item->category = $cat;

        // FIXME should the user be pulled from metadata as well?
        $user = null;
        $user = @$recent['user']; // the @ spares time repeating lookup
        $item->author = '';

        if($user && $conf['useacl'] && $auth){
            $userInfo = $auth->getUserData($user);
            $item->author = $userInfo['name'];
            if($guardmail) {
            //cannot obfuscate because some RSS readers may check validity
                $item->authorEmail = $user.'@'.$recent['ip'];
            }else{
                $item->authorEmail = $userInfo['mail'];
            }
        }elseif($user){
            // this happens when no ACL but some Apache auth is used
            $item->author      = $user;
            $item->authorEmail = $user.'@'.$recent['ip'];
        }else{
            $item->authorEmail = 'anonymous@'.$recent['ip'];
        }
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
        $item = new FeedItem();

        $id   = $row['id'];
        $date = filemtime(wikiFN($id));
        $meta = p_get_metadata($id);

        if($conf['useheading'] && $meta['title']){
            $item->title = $meta['title'];
        }else{
            $item->title = $id;
        }

        $item->link        = wl($id,'rev='.$date,true,'&');
        $item->description = $meta['description']['abstract'];
        $item->date        = date('r',$date);
        $rss->addItem($item);
  }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
?>
