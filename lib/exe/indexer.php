<?php
/**
 * DokuWiki indexer
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */
if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/../../');
define('DOKU_DISABLE_GZIP_OUTPUT',1);
require_once(DOKU_INC.'inc/init.php');
require_once(DOKU_INC.'inc/auth.php');
require_once(DOKU_INC.'inc/events.php');
session_write_close();  //close session
if(!defined('NL')) define('NL',"\n");

// Version tag used to force rebuild on upgrade
define('INDEXER_VERSION', 2);

// keep running after browser closes connection
@ignore_user_abort(true);

// check if user abort worked, if yes send output early
if(@ignore_user_abort() && !$conf['broken_iua']){
    sendGIF(); // send gif
    $defer = false;
}else{
    $defer = true;
}

$ID = cleanID($_REQUEST['id']);

// Catch any possible output (e.g. errors)
if(!$_REQUEST['debug']) ob_start();

// run one of the jobs
$tmp = array();
$evt = new Doku_Event('INDEXER_TASKS_RUN', $tmp);
if ($evt->advise_before()) {
  runIndexer() or 
  metaUpdate() or 
  runSitemapper() or 
  runTrimRecentChanges() or 
  $evt->advise_after();
}
if($defer) sendGIF();

if(!$_REQUEST['debug']) ob_end_clean();
exit;

// --------------------------------------------------------------------

/**
 * Trims the recent changes cache (or imports the old changelog) as needed.
 *
 * @author Ben Coburn <btcoburn@silicodon.net>
 */
function runTrimRecentChanges() {
    global $conf;

    // Import old changelog (if needed)
    // Uses the imporoldchangelog plugin to upgrade the changelog automaticaly.
    // FIXME: Remove this from runTrimRecentChanges when it is no longer needed.
    if (isset($conf['changelog_old']) &&
        @file_exists($conf['changelog_old']) && !@file_exists($conf['changelog']) &&
        !@file_exists($conf['changelog'].'_importing') && !@file_exists($conf['changelog'].'_tmp')) {
            $tmp = array(); // no event data
            trigger_event('TEMPORARY_CHANGELOG_UPGRADE_EVENT', $tmp);
            return true;
    }

    // Trim the Recent Changes
    // Trims the recent changes cache to the last $conf['changes_days'] recent
    // changes or $conf['recent'] items, which ever is larger.
    // The trimming is only done once a day.
    if (@file_exists($conf['changelog']) &&
        (filectime($conf['changelog'])+86400)<time() &&
        !@file_exists($conf['changelog'].'_tmp')) {
            io_lock($conf['changelog']);
            $lines = file($conf['changelog']);
            if (count($lines)<=$conf['recent']) {
                // nothing to trim
                io_unlock($conf['changelog']);
                return false;
            }

            io_saveFile($conf['changelog'].'_tmp', '');          // presave tmp as 2nd lock
            $trim_time = time() - $conf['recent_days']*86400;
            $out_lines = array();

            for ($i=0; $i<count($lines); $i++) {
              $log = parseChangelogLine($lines[$i]);
              if ($log === false) continue;                      // discard junk
              if ($log['date'] < $trim_time) {
                $old_lines[$log['date'].".$i"] = $lines[$i];     // keep old lines for now (append .$i to prevent key collisions)
              } else {
                $out_lines[$log['date'].".$i"] = $lines[$i];     // definitely keep these lines
              }
            }

            // sort the final result, it shouldn't be necessary,
            //   however the extra robustness in making the changelog cache self-correcting is worth it
            ksort($out_lines);
            $extra = $conf['recent'] - count($out_lines);        // do we need extra lines do bring us up to minimum
            if ($extra > 0) {
              ksort($old_lines);
              $out_lines = array_merge(array_slice($old_lines,-$extra),$out_lines);
            }

            // save trimmed changelog
            io_saveFile($conf['changelog'].'_tmp', implode('', $out_lines));
            @unlink($conf['changelog']);
            if (!rename($conf['changelog'].'_tmp', $conf['changelog'])) {
                // rename failed so try another way...
                io_unlock($conf['changelog']);
                io_saveFile($conf['changelog'], implode('', $out_lines));
                @unlink($conf['changelog'].'_tmp');
            } else {
                io_unlock($conf['changelog']);
            }
            return true;
    }

    // nothing done
    return false;
}

/**
 * Runs the indexer for the current page
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function runIndexer(){
    global $ID;
    global $conf;
    print "runIndexer(): started".NL;

    // Move index files (if needed)
    // Uses the importoldindex plugin to upgrade the index automatically.
    // FIXME: Remove this from runIndexer when it is no longer needed.
    if (@file_exists($conf['cachedir'].'/page.idx') &&
        (!@file_exists($conf['indexdir'].'/page.idx') ||
         !filesize($conf['indexdir'].'/page.idx'))  &&
        !@file_exists($conf['indexdir'].'/index_importing')) {
        echo "trigger TEMPORARY_INDEX_UPGRADE_EVENT\n";
        $tmp = array(); // no event data
        trigger_event('TEMPORARY_INDEX_UPGRADE_EVENT', $tmp);
    }

    if(!$ID) return false;

    // check if indexing needed
    $idxtag = metaFN($ID,'.indexed');
    if(@file_exists($idxtag)){
        if(io_readFile($idxtag) >= INDEXER_VERSION){
            $last = @filemtime($idxtag);
            if($last > @filemtime(wikiFN($ID))){
                print "runIndexer(): index for $ID up to date".NL;
                return false;
            }
        }
    }

    // try to aquire a lock
    $lock = $conf['lockdir'].'/_indexer.lock';
    while(!@mkdir($lock,$conf['dmode'])){
        usleep(50);
        if(time()-@filemtime($lock) > 60*5){
            // looks like a stale lock - remove it
            @rmdir($lock);
            print "runIndexer(): stale lock removed".NL;
        }else{
            print "runIndexer(): indexer locked".NL;
            return false;
        }
    }
    if($conf['dperm']) chmod($lock, $conf['dperm']);

    require_once(DOKU_INC.'inc/indexer.php');

    // upgrade to version 2
    if (!@file_exists($conf['indexdir'].'/pageword.idx'))
        idx_upgradePageWords();

    // do the work
    idx_addPage($ID);

    // we're finished - save and free lock
    io_saveFile(metaFN($ID,'.indexed'),INDEXER_VERSION);
    @rmdir($lock);
    print "runIndexer(): finished".NL;
    return true;
}

/**
 * Will render the metadata for the page if not exists yet
 *
 * This makes sure pages which are created from outside DokuWiki will
 * gain their data when viewed for the first time.
 */
function metaUpdate(){
    global $ID;
    print "metaUpdate(): started".NL;

    if(!$ID) return false;
    $file = metaFN($ID, '.meta');
    echo "meta file: $file".NL;

    // rendering needed?
    if (@file_exists($file)) return false;
    if (!@file_exists(wikiFN($ID))) return false;

    require_once(DOKU_INC.'inc/common.php');
    require_once(DOKU_INC.'inc/parserutils.php');
    global $conf;


    // gather some additional info from changelog
    $info = io_grep($conf['changelog'],
                    '/^(\d+)\t(\d+\.\d+\.\d+\.\d+)\t'.preg_quote($ID,'/').'\t([^\t]+)\t([^\t\n]+)/',
                    0,true);

    $meta = array();
    if(!empty($info)){
        $meta['date']['created'] = $info[0][1];
        foreach($info as $item){
            if($item[4] != '*'){
                $meta['date']['modified'] = $item[1];
                if($item[3]){
                    $meta['contributor'][$item[3]] = $item[3];
                }
            }
        }
    }

    $meta = p_render_metadata($ID, $meta);
    io_saveFile($file, serialize($meta));

    echo "metaUpdate(): finished".NL;
    return true;
}

/**
 * Builds a Google Sitemap of all public pages known to the indexer
 *
 * The map is placed in the root directory named sitemap.xml.gz - This
 * file needs to be writable!
 *
 * @author Andreas Gohr
 * @link   https://www.google.com/webmasters/sitemaps/docs/en/about.html
 */
function runSitemapper(){
    global $conf;
    print "runSitemapper(): started".NL;
    if(!$conf['sitemap']) return false;

    if($conf['compression'] == 'bz2' || $conf['compression'] == 'gz'){
        $sitemap = 'sitemap.xml.gz';
    }else{
        $sitemap = 'sitemap.xml';
    }
    print "runSitemapper(): using $sitemap".NL;

    if(@file_exists(DOKU_INC.$sitemap)){
        if(!is_writable(DOKU_INC.$sitemap)) return false;
    }else{
        if(!is_writable(DOKU_INC)) return false;
    }

    if(@filesize(DOKU_INC.$sitemap) && 
       @filemtime(DOKU_INC.$sitemap) > (time()-($conf['sitemap']*60*60*24))){
       print 'runSitemapper(): Sitemap up to date'.NL;
       return false;
    }

    $pages = file($conf['indexdir'].'/page.idx');
    print 'runSitemapper(): creating sitemap using '.count($pages).' pages'.NL;

    // build the sitemap
    ob_start();
    print '<?xml version="1.0" encoding="UTF-8"?>'.NL;
    print '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.NL;
    foreach($pages as $id){
        $id = trim($id);
        $file = wikiFN($id);

        //skip hidden, non existing and restricted files
        if(isHiddenPage($id)) continue;
        $date = @filemtime($file);
        if(!$date) continue;
        if(auth_aclcheck($id,'','') < AUTH_READ) continue;

        print '  <url>'.NL;
        print '    <loc>'.wl($id,'',true).'</loc>'.NL;
        print '    <lastmod>'.date_iso8601($date).'</lastmod>'.NL;
        print '  </url>'.NL;
    }
    print '</urlset>'.NL;
    $data = ob_get_contents();
    ob_end_clean();

    //save the new sitemap
    io_saveFile(DOKU_INC.$sitemap,$data);

    //ping search engines...
    $http = new DokuHTTPClient();
    $http->timeout = 8;

    //ping google
    print 'runSitemapper(): pinging google'.NL;
    $url  = 'http://www.google.com/webmasters/sitemaps/ping?sitemap=';
    $url .= urlencode(DOKU_URL.$sitemap);
    $resp = $http->get($url);
    if($http->error) print 'runSitemapper(): '.$http->error.NL;
    print 'runSitemapper(): '.preg_replace('/[\n\r]/',' ',strip_tags($resp)).NL;

    //ping yahoo
    print 'runSitemapper(): pinging yahoo'.NL;
    $url  = 'http://search.yahooapis.com/SiteExplorerService/V1/updateNotification?appid=dokuwiki&url=';
    $url .= urlencode(DOKU_URL.$sitemap);
    $resp = $http->get($url);
    if($http->error) print 'runSitemapper(): '.$http->error.NL;
    print 'runSitemapper(): '.preg_replace('/[\n\r]/',' ',strip_tags($resp)).NL;

    //ping microsoft
    print 'runSitemapper(): pinging microsoft'.NL;
    $url  = 'http://search.live.com/ping?sitemap=';
    $url .= urlencode(DOKU_URL.$sitemap);
    $resp = $http->get($url);
    if($http->error) print 'runSitemapper(): '.$http->error.NL;
    print 'runSitemapper(): '.preg_replace('/[\n\r]/',' ',strip_tags($resp)).NL;

    print 'runSitemapper(): finished'.NL;
    return true;
}

/**
 * Formats a timestamp as ISO 8601 date
 *
 * @author <ungu at terong dot com>
 * @link http://www.php.net/manual/en/function.date.php#54072
 */
function date_iso8601($int_date) {
   //$int_date: current date in UNIX timestamp
   $date_mod = date('Y-m-d\TH:i:s', $int_date);
   $pre_timezone = date('O', $int_date);
   $time_zone = substr($pre_timezone, 0, 3).":".substr($pre_timezone, 3, 2);
   $date_mod .= $time_zone;
   return $date_mod;
}

/**
 * Just send a 1x1 pixel blank gif to the browser
 * 
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Harry Fuecks <fuecks@gmail.com>
 */
function sendGIF(){
    if($_REQUEST['debug']){
        header('Content-Type: text/plain');
        return;
    }
    $img = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAEALAAAAAABAAEAAAIBTAA7');
    header('Content-Type: image/gif');
    header('Content-Length: '.strlen($img));
    header('Connection: Close');
    print $img;
    flush();
    // Browser should drop connection after this
    // Thinks it's got the whole image
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
// No trailing PHP closing tag - no output please!
// See Note at http://www.php.net/manual/en/language.basic-syntax.instruction-separation.php
