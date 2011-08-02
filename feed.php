<?php
/**
 * XML feed export
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/');
require_once(DOKU_INC.'inc/init.php');

//close session
session_write_close();

// get params
$opt = rss_parseOptions();

// the feed is dynamic - we need a cache for each combo
// (but most people just use the default feed so it's still effective)
$cache = getCacheName(join('',array_values($opt)).$_SERVER['REMOTE_USER'],'.feed');
$key   = join('', array_values($opt)) . $_SERVER['REMOTE_USER'];
$cache = new cache($key, '.feed');

// prepare cache depends
$depends['files'] = getConfigFiles('main');
$depends['age']   = $conf['rss_update'];
$depends['purge'] = isset($_REQUEST['purge']);

// check cacheage and deliver if nothing has changed since last
// time or the update interval has not passed, also handles conditional requests
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex');
if($cache->useCache($depends)) {
    http_conditionalRequest($cache->_time);
    if($conf['allowdebug']) header("X-CacheUsed: $cache->cache");
    print $cache->retrieveCache();
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
$image->url = tpl_getFavicon(true);
$image->link = DOKU_URL;
$rss->image = $image;

$data = null;
$modes = array('list'   => 'rssListNamespace',
               'search' => 'rssSearch',
               'recent' => 'rssRecentChanges');
if (isset($modes[$opt['feed_mode']])) {
    $data = $modes[$opt['feed_mode']]($opt);
} else {
    $eventData = array(
        'opt'  => &$opt,
        'data' => &$data,
    );
    $event = new Doku_Event('FEED_MODE_UNKNOWN', $eventData);
    if ($event->advise_before(true)) {
        echo sprintf('<error>Unknown feed mode %s</error>', hsc($opt['feed_mode']));
        exit;
    }
    $event->advise_after();
}

rss_buildItems($rss, $data, $opt);
$feed = $rss->createFeed($opt['feed_type'],'utf-8');

// save cachefile
$cache->storeCache($feed);

// finally deliver
print $feed;

// ---------------------------------------------------------------- //

/**
 * Get URL parameters and config options and return an initialized option array
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function rss_parseOptions(){
    global $conf;

    $opt = array();

    foreach(array(
                  // Basic feed properties
                  // Plugins may probably want to add new values to these
                  // properties for implementing own feeds

                  // One of: list, search, recent
                  'feed_mode'    => array('mode', 'recent'),
                  // One of: diff, page, rev, current
                  'link_to'      => array('linkto', $conf['rss_linkto']),
                  // One of: abstract, diff, htmldiff, html
                  'item_content' => array('content', $conf['rss_content']),

                  // Special feed properties
                  // These are only used by certain feed_modes

                  // String, used for feed title, in list and rc mode
                  'namespace'    => array('ns', null),
                  // Positive integer, only used in rc mode
                  'items'        => array('num', $conf['recent']),
                  // Boolean, only used in rc mode
                  'show_minor'   => array('minor', false),
                  // String, only used in search mode
                  'search_query' => array('q', null),
                // One of: pages, media, both
                  'content_type' => array('view', 'both')

                 ) as $name => $val) {
        $opt[$name] = (isset($_REQUEST[$val[0]]) && !empty($_REQUEST[$val[0]]))
                      ? $_REQUEST[$val[0]] : $val[1];
    }

    $opt['items']        = max(0, (int)  $opt['items']);
    $opt['show_minor']   = (bool) $opt['show_minor'];

    $opt['guardmail']  = ($conf['mailguard'] != '' && $conf['mailguard'] != 'none');

    $type = valid_input_set('type', array('rss','rss2','atom','atom1','rss1',
                                          'default' => $conf['rss_type']),
                            $_REQUEST);
    switch ($type){
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

    $eventData = array(
        'opt' => &$opt,
    );
    trigger_event('FEED_OPTS_POSTPROCESS', $eventData);
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
    global $auth;

    $eventData = array(
        'rss' => &$rss,
        'data' => &$data,
        'opt' => &$opt,
    );
    $event = new Doku_Event('FEED_DATA_PROCESS', $eventData);
    if ($event->advise_before(false)){
        foreach($data as $ditem){
            if(!is_array($ditem)){
                // not an array? then only a list of IDs was given
                $ditem = array( 'id' => $ditem );
            }

            $item = new FeedItem();
            $id   = $ditem['id'];
            if(!$ditem['media']) {
                $meta = p_get_metadata($id);
            }

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
                    if ($ditem['media']) {
                        $item->link = media_managerURL(array('image' => $id,
                            'ns' => getNS($id),
                            'rev' => $date), '&', true);
                    } else {
                        $item->link = wl($id,'rev='.$date,true,'&', true);
                    }
                    break;
                case 'rev':
                    if ($ditem['media']) {
                        $item->link = media_managerURL(array('image' => $id,
                            'ns' => getNS($id),
                            'rev' => $date,
                            'tab_details' => 'history'), '&', true);
                    } else {
                        $item->link = wl($id,'do=revisions&rev='.$date,true,'&');
                    }
                    break;
                case 'current':
                    if ($ditem['media']) {
                        $item->link = media_managerURL(array('image' => $id,
                            'ns' => getNS($id)), '&', true);
                    } else {
                        $item->link = wl($id, '', true,'&');
                    }
                    break;
                case 'diff':
                default:
                    if ($ditem['media']) {
                        $item->link = media_managerURL(array('image' => $id,
                            'ns' => getNS($id),
                            'rev' => $date,
                            'tab_details' => 'history',
                            'mediado' => 'diff'), '&', true);
                    } else {
                        $item->link = wl($id,'rev='.$date.'&do=diff',true,'&');
                    }
            }

            // add item content
            switch ($opt['item_content']){
                case 'diff':
                case 'htmldiff':
                    if ($ditem['media']) {
                        $revs = getRevisions($id, 0, 1, 8192, true);
                        $rev = $revs[0];
                        $src_r = '';
                        $src_l = '';

                        if ($size = media_image_preview_size($id, false, new JpegMeta(mediaFN($id)), 300)) {
                            $more = 'w='.$size[0].'&h='.$size[1].'t='.@filemtime(mediaFN($id));
                            $src_r = ml($id, $more);
                        }
                        if ($rev && $size = media_image_preview_size($id, $rev, new JpegMeta(mediaFN($id, $rev)), 300)){
                            $more = 'rev='.$rev.'&w='.$size[0].'&h='.$size[1];
                            $src_l = ml($id, $more);
                        }
                        $content = '';
                        if ($src_r) {
                            $content  = '<table>';
                            $content .= '<tr><th width="50%">'.$rev.'</th>';
                            $content .= '<th width="50%">'.$lang['current'].'</th></tr>';
                            $content .= '<tr align="center"><td><img src="'.$src_l.'" alt="" /></td><td>';
                            $content .= '<img src="'.$src_r.'" alt="'.$id.'" /></td></tr>';
                            $content .= '</table>';
                        }

                    } else {
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
                    }
                    break;
                case 'html':
                    if ($ditem['media']) {
                        if ($size = media_image_preview_size($id, false, new JpegMeta(mediaFN($id)))) {
                            $more = 'w='.$size[0].'&h='.$size[1].'t='.@filemtime(mediaFN($id));
                            $src = ml($id, $more);
                            $content = '<img src="'.$src.'" alt="'.$id.'" />';
                        } else {
                            $content = '';
                        }
                    } else {
                        $content = p_wiki_xhtml($id,$date,false);
                        // no TOC in feeds
                        $content = preg_replace('/(<!-- TOC START -->).*(<!-- TOC END -->)/s','',$content);

                        // make URLs work when canonical is not set, regexp instead of rerendering!
                        if(!$conf['canonical']){
                            $base = preg_quote(DOKU_REL,'/');
                            $content = preg_replace('/(<a href|<img src)="('.$base.')/s','$1="'.DOKU_URL,$content);
                        }
                    }

                    break;
                case 'abstract':
                default:
                    if ($ditem['media']) {
                        if ($size = media_image_preview_size($id, false, new JpegMeta(mediaFN($id)))) {
                            $more = 'w='.$size[0].'&h='.$size[1].'t='.@filemtime(mediaFN($id));
                            $src = ml($id, $more);
                            $content = '<img src="'.$src.'" alt="'.$id.'" />';
                        } else {
                            $content = '';
                        }
                    } else {
                        $content = $meta['description']['abstract'];
                    }
            }
            $item->description = $content; //FIXME a plugin hook here could be senseful

            // add user
            # FIXME should the user be pulled from metadata as well?
            $user = @$ditem['user']; // the @ spares time repeating lookup
            $item->author = '';
            if($user && $conf['useacl'] && $auth){
                $userInfo = $auth->getUserData($user);
                if ($userInfo){
                    switch ($conf['showuseras']){
                        case 'username':
                            $item->author = $userInfo['name'];
                            break;
                        default:
                            $item->author = $user;
                            break;
                    }
                } else {
                    $item->author = $user;
                }
                if($userInfo && !$opt['guardmail']){
                    $item->authorEmail = $userInfo['mail'];
                }else{
                    //cannot obfuscate because some RSS readers may check validity
                    $item->authorEmail = $user.'@'.$ditem['ip'];
                }
            }elseif($user){
                // this happens when no ACL but some Apache auth is used
                $item->author      = $user;
                $item->authorEmail = $user.'@'.$ditem['ip'];
            }else{
                $item->authorEmail = 'anonymous@'.$ditem['ip'];
            }

            // add category
            if(isset($meta['subject'])) {
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
    $event->advise_after();
}


/**
 * Add recent changed pages to the feed object
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function rssRecentChanges($opt){
    global $conf;
    $flags = RECENTS_SKIP_DELETED;
    if(!$opt['show_minor']) $flags += RECENTS_SKIP_MINORS;
    if($opt['content_type'] == 'media' && $conf['mediarevisions']) $flags += RECENTS_MEDIA_CHANGES;
    if($opt['content_type'] == 'both' && $conf['mediarevisions']) $flags += RECENTS_MEDIA_PAGES_MIXED;

    $recents = getRecents(0,$opt['items'],$opt['namespace'],$flags);
    return $recents;
}

/**
 * Add all pages of a namespace to the feed object
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function rssListNamespace($opt){
    require_once(DOKU_INC.'inc/search.php');
    global $conf;

    $ns=':'.cleanID($opt['namespace']);
    $ns=str_replace(':','/',$ns);

    $data = array();
    sort($data);
    search($data,$conf['datadir'],'search_list','',$ns);

    return $data;
}

/**
 * Add the result of a full text search to the feed object
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function rssSearch($opt){
    if(!$opt['search_query']) return;

    require_once(DOKU_INC.'inc/fulltext.php');
    $data = ft_pageSearch($opt['search_query'],$poswords);
    $data = array_keys($data);

    return $data;
}

//Setup VIM: ex: et ts=4 :
