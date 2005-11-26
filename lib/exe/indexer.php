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

// keep running after browser closes connection
@ignore_user_abort(true);

// send gif
sendGIF();

// Catch any possible output (e.g. errors)
// - probably not needed but better safe...
ob_start();

// Now start work
require_once(DOKU_INC.'inc/utf8.php');
require_once(DOKU_INC.'inc/auth.php');

// run one of the jobs
runIndexer() or runSitemapper();

ob_end_clean();
exit;

// --------------------------------------------------------------------

/**
 * Runs the indexer for the current page
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function runIndexer(){
    global $conf;

    $ID = cleanID($_REQUEST['id']);
    if(!$ID) return false;

    // check if indexing needed
    $last = @filemtime(metaFN($ID,'.indexed'));
    if($last > @filemtime(wikiFN($ID))) return false;

    // try to aquire a lock
    $lock = $conf['lockdir'].'/_indexer.lock';
    while(!@mkdir($lock,0777)){
        if(time()-@filemtime($lock) > 60*5){
            // looks like a stale lock - remove it
            @rmdir($lock);
        }else{
            return false;
        }
    }

    require_once(DOKU_INC.'inc/indexer.php');

    // do the work
    idx_addPage($ID);

    // we're finished - save and free lock
    io_saveFile(metaFN($ID,'.indexed'),' ');
    @rmdir($lock);
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
    if(!$conf['sitemap']) return false;
    if(!defined('NL')) define('NL',"\n");

    if($conf['usegzip']){
        $sitemap = DOKU_INC.'sitemap.xml.gz';
    }else{
        $sitemap = DOKU_INC.'sitemap.xml';
    }


    if(!is_writable($sitemap)) return false;
    if(@filesize($sitemap) && 
       @filemtime($sitemap) > (time()-($conf['sitemap']*60*60*24))){
       return false;
    }

    ob_start();
    $pages = file($conf['cachedir'].'/page.idx');

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
        print '    <lastmod>'.date('Y-m-d\TH:i:s',$date).'</lastmod>'.NL;
        print '  </url>'.NL;
    }
    print '</urlset>'.NL;

    $data = ob_get_contents();
    ob_end_clean();

    io_saveFile($sitemap,$data);
    return true;
}

/**
 * Just send a 1x1 pixel blank gif to the browser
 * 
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Harry Fuecks <fuecks@gmail.com>
 */
function sendGIF(){
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
