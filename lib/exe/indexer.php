<?php
/**
 * DokuWiki indexer
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
require_once(DOKU_INC.'inc/init.php');
require_once(DOKU_INC.'inc/auth.php');
session_write_close();  //close session
if(!defined('NL')) define('NL',"\n");

// keep running after browser closes connection
@ignore_user_abort(true);

// send gif
sendGIF();

// Catch any possible output (e.g. errors)
if(!$_REQUEST['debug']) ob_start();

// run one of the jobs
runIndexer() or runSitemapper();

if(!$_REQUEST['debug']) ob_end_clean();
exit;

// --------------------------------------------------------------------

/**
 * Runs the indexer for the current page
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function runIndexer(){
    global $conf;
    print "runIndexer(): started".NL;

    $ID = cleanID($_REQUEST['id']);
    if(!$ID) return false;

    // check if indexing needed
    $last = @filemtime(metaFN($ID,'.indexed'));
    if($last > @filemtime(wikiFN($ID))){
        print "runIndexer(): index for $ID up to date".NL;
        return false;
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

    // do the work
    idx_addPage($ID);

    // we're finished - save and free lock
    io_saveFile(metaFN($ID,'.indexed'),' ');
    @rmdir($lock);
    print "runIndexer(): finished".NL;
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

    if($conf['usegzip']){
        $sitemap = DOKU_INC.'sitemap.xml.gz';
    }else{
        $sitemap = DOKU_INC.'sitemap.xml';
    }
    print "runSitemapper(): using $sitemap".NL;

    if(!is_writable($sitemap)) return false;
    if(@filesize($sitemap) && 
       @filemtime($sitemap) > (time()-($conf['sitemap']*60*60*24))){
       print 'runSitemapper(): Sitemap up to date'.NL;
       return false;
    }

    $pages = file($conf['cachedir'].'/page.idx');
    print 'runSitemapper(): creating sitemap using '.count($pages).' pages'.NL;

    // build the sitemap
    ob_start();
    print '<?xml version="1.0" encoding="UTF-8"?>'.NL;
    print '<urlset xmlns="http://www.google.com/schemas/sitemap/0.84">'.NL;
    foreach($pages as $id){
        $id = trim($id);
        $file = wikiFN($id);

        //skip hidden, non existing and restricted files
        if(isHiddenPage($id)) return false;
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
    io_saveFile($sitemap,$data);

    print 'runSitemapper(): pinging google'.NL;
    //ping google
    $url  = 'http://www.google.com/webmasters/sitemaps/ping?sitemap=';
    $url .= urlencode(DOKU_URL.$sitemap);
    $http = new DokuHTTPClient();
    $http->get($url);
    if($http->error) print 'runSitemapper(): '.$http->error.NL;

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
