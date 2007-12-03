<?php
/**
 * XML feed export
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

  if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/');
  require_once(DOKU_INC.'inc/init.php');
  require_once(DOKU_INC.'inc/common.php');
  require_once(DOKU_INC.'inc/events.php');
  require_once(DOKU_INC.'inc/parserutils.php');
  require_once(DOKU_INC.'inc/feedcreator.class.php');
  require_once(DOKU_INC.'inc/auth.php');
  require_once(DOKU_INC.'inc/pageutils.php');

  //close session
  session_write_close();

  // get params
  $opt = rss_parseOptions();

  // the feed is dynamic - we need a cache for each combo
  // (but most people just use the default feed so it's still effective)
  $cache = getCacheName(join('',array_values($opt)).$_SERVER['REMOTE_USER'],'.feed');
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
  $rss->title = $conf['title'].(($opt['namespace']) ? ' '.$opt['namespace'] : '');
  $rss->link  = DOKU_URL;
  $rss->syndicationURL = DOKU_URL.'feed.php';
  $rss->cssStyleSheet  = DOKU_URL.'lib/exe/css.php?s=feed';

  $image = new FeedImage();
  $image->title = $conf['title'];
  $image->url = DOKU_URL."lib/images/favicon.ico";
  $image->link = DOKU_URL;
  $rss->image = $image;

  if($opt['feed_mode'] == 'list'){
    rssListNamespace($rss,$opt);
  }elseif($opt['feed_mode'] == 'search'){
    rssSearch($rss,$opt);
  }else{
    rssRecentChanges($rss,$opt);
  }

  $feed = $rss->createFeed($opt['feed_type'],'utf-8');

  // save cachefile
  io_saveFile($cache,$feed);

  // finally deliver
  print $feed;

// ---------------------------------------------------------------- //

/**
 * Get URL parameters and config options and return a initialized option array
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function rss_parseOptions(){
    global $conf;

    $opt['items']        = (int) $_REQUEST['num'];
    $opt['feed_type']    = $_REQUEST['type'];
    $opt['feed_mode']    = $_REQUEST['mode'];
    $opt['show_minor']   = $_REQUEST['minor'];
    $opt['namespace']    = $_REQUEST['ns'];
    $opt['link_to']      = $_REQUEST['linkto'];
    $opt['item_content'] = $_REQUEST['content'];
    $opt['search_query'] = $_REQUEST['q'];

    if(!$opt['feed_type'])    $opt['feed_type']    = $conf['rss_type'];
    if(!$opt['item_content']) $opt['item_content'] = $conf['rss_content'];
    if(!$opt['link_to'])      $opt['link_to']      = $conf['rss_linkto'];
    if(!$opt['items'])        $opt['items']        = $conf['recent'];
    $opt['guardmail']  = ($conf['mailguard'] != '' && $conf['mailguard'] != 'none');

    switch ($opt['feed_type']){
      case 'rss':
         $opt['feed_type'] = 'RSS0.91';
         $opt['mime_type'] = 'text/xml';
         break;
      case 'rss2':
         $opt['feed_type'] = 'RSS2.0';
         $opt['mime_type'] = 'text/xml';
         break;
      case 'atom':
         $opt['feed_type'] = 'ATOM0.3';
         $opt['mime_type'] = 'application/xml';
         break;
      case 'atom1':
         $opt['feed_type'] = 'ATOM1.0';
         $opt['mime_type'] = 'application/atom+xml';
         break;
      default:
         $opt['feed_type'] = 'RSS1.0';
         $opt['mime_type'] = 'application/xml';
    }
    return $opt;
}

/**
 * Add recent changed pages to a feed object
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @param  object $rss - the FeedCreator Object
 * @param  array $data - the items to add
 * @param  array $opt  - the feed options
 */
function rss_buildItems(&$rss,&$data,$opt){
    global $conf;
    global $lang;


    foreach($data as $ditem){
        if(!is_array($ditem)){
            // not an array? then only a list of IDs was given
            $ditem = array( 'id' => $ditem );
        }

        $item = new FeedItem();
        $id   = $ditem['id'];
        $meta = p_get_metadata($id);

        // add date
        if($ditem['date']){
            $date = $ditem['date'];
        }elseif($meta['date']['modified']){
            $date = $meta['date']['modified'];
        }else{
            $date = @filemtime(wikiFN($id));
        }
        if($date) $item->date = date('r',$date);

        // add title
        if($conf['useheading'] && $meta['title']){
            $item->title = $meta['title'];
        }else{
            $item->title = $ditem['id'];
        }
        if($conf['rss_show_summary'] && !empty($ditem['sum'])){
            $item->title .= ' - '.strip_tags($ditem['sum']);
        }

        // add item link
        switch ($opt['link_to']){
            case 'page':
                $item->link = wl($id,'rev='.$date,true,'&');
                break;
            case 'rev':
                $item->link = wl($id,'do=revisions&rev='.$date,true,'&');
                break;
            case 'current':
                $item->link = wl($id, '', true,'&');
                break;
            case 'diff':
            default:
                $item->link = wl($id,'rev='.$date.'&do=diff',true,'&');
        }

        // add item content
        switch ($opt['item_content']){
            case 'diff':
            case 'htmldiff':
                require_once(DOKU_INC.'inc/DifferenceEngine.php');
                $revs = getRevisions($id, 0, 1);
                $rev = $revs[0];

                if($rev){
                    $df  = new Diff(explode("\n",htmlspecialchars(rawWiki($id,$rev))),
                                    explode("\n",htmlspecialchars(rawWiki($id,''))));
                }else{
                    $df  = new Diff(array(''),
                                    explode("\n",htmlspecialchars(rawWiki($id,''))));
                }

                if($opt['item_content'] == 'htmldiff'){
                    $tdf = new TableDiffFormatter();
                    $content  = '<table>';
                    $content .= '<tr><th colspan="2" width="50%">'.$rev.'</th>';
                    $content .= '<th colspan="2" width="50%">'.$lang['current'].'</th></tr>';
                    $content .= $tdf->format($df);
                    $content .= '</table>';
                }else{
                    $udf = new UnifiedDiffFormatter();
                    $content = "<pre>\n".$udf->format($df)."\n</pre>";
                }
                break;
            case 'html':
                $content = p_wiki_xhtml($id,$date,false);
                // no TOC in feeds
                $content = preg_replace('/(<!-- TOC START -->).*(<!-- TOC END -->)/s','',$content);

                // make URLs work when canonical is not set, regexp instead of rerendering!
                if(!$conf['canonical']){
                    $base = preg_quote(DOKU_REL,'/');
                    $content = preg_replace('/(<a href|<img src)="('.$base.')/s','$1="'.DOKU_URL,$content);
                }

                break;
            case 'abstract':
            default:
                $content = $meta['description']['abstract'];
        }
        $item->description = $content; //FIXME a plugin hook here could be senseful


        // add user
        # FIXME should the user be pulled from metadata as well?
        $user = null;
        $user = @$ditem['user']; // the @ spares time repeating lookup
        $item->author = '';
        if($user && $conf['useacl'] && $auth){
            $userInfo = $auth->getUserData($user);
            $item->author = $userInfo['name'];
            if($userInfo && !$opt['guardmail']){
                $item->authorEmail = $userInfo['mail'];
            }else{
                //cannot obfuscate because some RSS readers may check validity
                $item->authorEmail = $user.'@'.$recent['ip'];
            }
        }elseif($user){
            // this happens when no ACL but some Apache auth is used
            $item->author      = $user;
            $item->authorEmail = $user.'@'.$recent['ip'];
        }else{
            $item->authorEmail = 'anonymous@'.$recent['ip'];
        }

        // add category
        if($meta['subject']){
            $item->category = $meta['subject'];
        }else{
           $cat = getNS($id);
           if($cat) $item->category = $cat;
        }

        // finally add the item to the feed object, after handing it to registered plugins
        $evdata = array('item'  => &$item,
                        'opt'   => &$opt,
                        'ditem' => &$ditem,
                        'rss'   => &$rss);
        $evt = new Doku_Event('FEED_ITEM_ADD', $evdata);
        if ($evt->advise_before()){
          $rss->addItem($item);
        }
        $evt->advise_after(); // for completeness
    }
}


/**
 * Add recent changed pages to the feed object
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function rssRecentChanges(&$rss,$opt){
    global $conf;
    global $auth;

    $flags = RECENTS_SKIP_DELETED;
    if(!$opt['show_minor']) $flags += RECENTS_SKIP_MINORS;

    $recents = getRecents(0,$opt['items'],$opt['namespace'],$flags);

    rss_buildItems($rss,$recents,$opt);
}

/**
 * Add all pages of a namespace to the feed object
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function rssListNamespace(&$rss,$opt){
    require_once(DOKU_INC.'inc/search.php');
    global $conf;

    $ns=':'.cleanID($opt['namespace']);
    $ns=str_replace(':','/',$ns);

    $data = array();
    sort($data);
    search($data,$conf['datadir'],'search_list','',$ns);

    rss_buildItems($rss,$data,$opt);
}

/**
 * Add the result of a full text search to the feed object
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function rssSearch(&$rss,$opt){
    if(!$opt['search_query']) return;

    require_once(DOKU_INC.'inc/fulltext.php');
    $data = array();
    $data = ft_pageSearch($opt['search_query'],$poswords);
    $data = array_keys($data);
    rss_buildItems($rss,$data,$opt);
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
?>
