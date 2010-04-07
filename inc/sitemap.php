<?php
/**
 * Sitemap handling functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michael Hamann <michael@content-space.de>
 */

if(!defined('DOKU_INC')) die('meh.');

/**
 * Builds a Google Sitemap of all public pages known to the indexer
 *
 * The map is placed in the cache directory named sitemap.xml.gz - This
 * file needs to be writable!
 *
 * @author Andreas Gohr
 * @link   https://www.google.com/webmasters/sitemaps/docs/en/about.html
 */
function sitemapGenerate(){
    global $conf;
    dbglog('sitemapGenerate(): started');
    if(!$conf['sitemap']) return false;

    $sitemap = sitemapGetFilePath();
    dbglog("runSitemapper(): using $sitemap");

    if(@file_exists($sitemap)){
        if(!is_writable($sitemap)) return false;
    }else{
        if(!is_writable(dirname($sitemap))) return false;
    }

    if(@filesize($sitemap) &&
       @filemtime($sitemap) > (time()-($conf['sitemap']*60*60*24))){
       dbglog('runSitemapper(): Sitemap up to date');
       return false;
    }

    $pages = idx_getIndex('page', '');
    dbglog('runSitemapper(): creating sitemap using '.count($pages).' pages');

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
    return io_saveFile($sitemap,$data);
}

function sitemapGetFilePath() {
    global $conf;

    $sitemap = $conf['cachedir'].'/sitemap.xml';
    if($conf['compression'] == 'bz2' || $conf['compression'] == 'gz'){
        $sitemap .= '.gz';
    }

    return $sitemap;
}

function sitemapPingSearchEngines() {
    //ping search engines...
    $http = new DokuHTTPClient();
    $http->timeout = 8;

    $encoded_sitemap_url = urlencode(wl('', array('do' => 'sitemap'), true, '&'));
    $ping_urls = array(
        'google' => 'http://www.google.com/webmasters/sitemaps/ping?sitemap='.$encoded_sitemap_url,
        'yahoo' => 'http://search.yahooapis.com/SiteExplorerService/V1/updateNotification?appid=dokuwiki&url='.$encoded_sitemap_url,
        'microsoft' => 'http://www.bing.com/webmaster/ping.aspx?siteMap='.$encoded_sitemap_url,
    );

    foreach ($ping_urls as $name => $url) {
        dbglog("sitemapPingSearchEngines(): pinging $name");
        $resp = $http->get($url);
        if($http->error) dbglog("runSitemapper(): $http->error");
        dbglog('runSitemapper(): '.preg_replace('/[\n\r]/',' ',strip_tags($resp)));
    }

    return true;
}
