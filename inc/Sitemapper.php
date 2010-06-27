<?php
/**
 * Sitemap handling functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michael Hamann <michael@content-space.de>
 */

if(!defined('DOKU_INC')) die('meh.');

class Sitemapper {
    /**
     * Builds a Google Sitemap of all public pages known to the indexer
     *
     * The map is placed in the cache directory named sitemap.xml.gz - This
     * file needs to be writable!
     *
     * @author Andreas Gohr
     * @link   https://www.google.com/webmasters/sitemaps/docs/en/about.html
     */
    public function generate(){
        global $conf;
        dbglog('sitemapGenerate(): started');
        if(!$conf['sitemap']) return false;

        $sitemap = Sitemapper::getFilePath();
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
        $items = array();

        // build the sitemap items
        foreach($pages as $id){
            //skip hidden, non existing and restricted files
            if(isHiddenPage($id)) continue;
            if(auth_aclcheck($id,'','') < AUTH_READ) continue;
            $items[] = SitemapItem::createFromID($id);
        }

        $eventData = array('items' => &$items, 'sitemap' => &$sitemap);
        $event = new Doku_Event('SITEMAP_GENERATE', $eventData);
        if ($event->advise_before(true)) {
            //save the new sitemap
            $result = io_saveFile($sitemap, Sitemapper::getXML($items));
        }
        $event->advise_after();

        return $result;
    }

    private function getXML($items) {
        ob_start();
        print '<?xml version="1.0" encoding="UTF-8"?>'.NL;
        print '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.NL;
        foreach ($items as $item) {
            print $item->toXML();
        }
        print '</urlset>'.NL;
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }


    public function getFilePath() {
        global $conf;

        $sitemap = $conf['cachedir'].'/sitemap.xml';
        if($conf['compression'] == 'bz2' || $conf['compression'] == 'gz'){
            $sitemap .= '.gz';
        }

        return $sitemap;
    }

    public function pingSearchEngines() {
        //ping search engines...
        $http = new DokuHTTPClient();
        $http->timeout = 8;

        $encoded_sitemap_url = urlencode(wl('', array('do' => 'sitemap'), true, '&'));
        $ping_urls = array(
            'google' => 'http://www.google.com/webmasters/sitemaps/ping?sitemap='.$encoded_sitemap_url,
            'yahoo' => 'http://search.yahooapis.com/SiteExplorerService/V1/updateNotification?appid=dokuwiki&url='.$encoded_sitemap_url,
            'microsoft' => 'http://www.bing.com/webmaster/ping.aspx?siteMap='.$encoded_sitemap_url,
        );

        $event = new Doku_Event('SITEMAP_PING', $ping_urls);
        if ($event->advise_before(true)) {
            foreach ($ping_urls as $name => $url) {
                dbglog("sitemapPingSearchEngines(): pinging $name");
                $resp = $http->get($url);
                if($http->error) dbglog("runSitemapper(): $http->error");
                dbglog('runSitemapper(): '.preg_replace('/[\n\r]/',' ',strip_tags($resp)));
            }
        }
        $event->advise_after();

        return true;
    }
}

class SitemapItem {
    public $url;
    public $lastmod;
    public $changefreq;
    public $priority;

    public function __construct($url, $lastmod, $changefreq = null, $priority = null) {
        $this->url = $url;
        $this->lastmod = $lastmod;
        $this->changefreq = $changefreq;
        $this->priority = $priority;
    }

    public static function createFromID($id, $changefreq = null, $priority = null) {
        $id = trim($id);
        $date = @filemtime(wikiFN($id));
        if(!$date) return NULL;
        return new SitemapItem(wl($id, '', true), $date, $changefreq, $priority);
    }

    public function toXML() {
        $result = '  <url>'.NL;
        $result .= '    <loc>'.hsc($this->url).'</loc>'.NL;
        $result .= '    <lastmod>'.date_iso8601($this->lastmod).'</lastmod>'.NL;
        if ($this->changefreq !== NULL)
            $result .= '    <changefreq>'.hsc($this->changefreq).'</changefreq>'.NL;
        if ($this->priority !== NULL)
            $result .= '    <priority>'.hsc($this->priority).'</priority>'.NL;
        $result .= '  </url>'.NL;
        return $result;
    }
}
