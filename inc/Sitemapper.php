<?php
/**
 * Sitemap handling functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michael Hamann <michael@content-space.de>
 */

if(!defined('DOKU_INC')) die('meh.');

/**
 * A class for building sitemaps and pinging search engines with the sitemap URL.
 *
 * @author Michael Hamann
 */
class Sitemapper {
    /**
     * Builds a Google Sitemap of all public pages known to the indexer
     *
     * The map is placed in the cache directory named sitemap.xml.gz - This
     * file needs to be writable!
     *
     * @author Michael Hamann
     * @author Andreas Gohr
     * @link   https://www.google.com/webmasters/sitemaps/docs/en/about.html
     * @link   http://www.sitemaps.org/
     *
     * @return bool
     */
    public static function generate(){
        global $conf;
        if($conf['sitemap'] < 1 || !is_numeric($conf['sitemap'])) return false;

        $sitemap = Sitemapper::getFilePath();

        if(file_exists($sitemap)){
            if(!is_writable($sitemap)) return false;
        }else{
            if(!is_writable(dirname($sitemap))) return false;
        }

        if(@filesize($sitemap) &&
           @filemtime($sitemap) > (time()-($conf['sitemap']*86400))){ // 60*60*24=86400
            dbglog('Sitemapper::generate(): Sitemap up to date');
            return false;
        }

        dbglog("Sitemapper::generate(): using $sitemap");

        $pages = idx_get_indexer()->getPages();
        dbglog('Sitemapper::generate(): creating sitemap using '.count($pages).' pages');
        $items = array();

        // build the sitemap items
        foreach($pages as $id){
            //skip hidden, non existing and restricted files
            if(isHiddenPage($id)) continue;
            if(auth_aclcheck($id,'',array()) < AUTH_READ) continue;
            $item = SitemapItem::createFromID($id);
            if ($item !== null)
                $items[] = $item;
        }

        $eventData = array('items' => &$items, 'sitemap' => &$sitemap);
        $event = new Doku_Event('SITEMAP_GENERATE', $eventData);
        if ($event->advise_before(true)) {
            //save the new sitemap
            $event->result = io_saveFile($sitemap, Sitemapper::getXML($items));
        }
        $event->advise_after();

        return $event->result;
    }

    /**
     * Builds the sitemap XML string from the given array auf SitemapItems.
     *
     * @param $items array The SitemapItems that shall be included in the sitemap.
     * @return string The sitemap XML.
     *
     * @author Michael Hamann
     */
    private static function getXML($items) {
        ob_start();
        echo '<?xml version="1.0" encoding="UTF-8"?>'.NL;
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.NL;
        foreach ($items as $item) {
            /** @var SitemapItem $item */
            echo $item->toXML();
        }
        echo '</urlset>'.NL;
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }

    /**
     * Helper function for getting the path to the sitemap file.
     *
     * @return string The path to the sitemap file.
     *
     * @author Michael Hamann
     */
    public static function getFilePath() {
        global $conf;

        $sitemap = $conf['cachedir'].'/sitemap.xml';
        if (self::sitemapIsCompressed()) {
            $sitemap .= '.gz';
        }

        return $sitemap;
    }

    /**
     * Helper function for checking if the sitemap is compressed
     *
     * @return bool If the sitemap file is compressed
     */
    public static function sitemapIsCompressed() {
        global $conf;
        return $conf['compression'] === 'bz2' || $conf['compression'] === 'gz';
    }

    /**
     * Pings search engines with the sitemap url. Plugins can add or remove
     * urls to ping using the SITEMAP_PING event.
     *
     * @author Michael Hamann
     *
     * @return bool
     */
    public static function pingSearchEngines() {
        //ping search engines...
        $http = new DokuHTTPClient();
        $http->timeout = 8;

        $encoded_sitemap_url = urlencode(wl('', array('do' => 'sitemap'), true, '&'));
        $ping_urls = array(
            'google'    => 'http://www.google.com/webmasters/sitemaps/ping?sitemap='.$encoded_sitemap_url,
            'microsoft' => 'http://www.bing.com/webmaster/ping.aspx?siteMap='.$encoded_sitemap_url,
            'yandex'    => 'http://blogs.yandex.ru/pings/?status=success&url='.$encoded_sitemap_url
        );

        $data = array('ping_urls' => $ping_urls,
                            'encoded_sitemap_url' => $encoded_sitemap_url
        );
        $event = new Doku_Event('SITEMAP_PING', $data);
        if ($event->advise_before(true)) {
            foreach ($data['ping_urls'] as $name => $url) {
                dbglog("Sitemapper::PingSearchEngines(): pinging $name");
                $resp = $http->get($url);
                if($http->error) dbglog("Sitemapper:pingSearchengines(): $http->error");
                dbglog('Sitemapper:pingSearchengines(): '.preg_replace('/[\n\r]/',' ',strip_tags($resp)));
            }
        }
        $event->advise_after();

        return true;
    }
}

/**
 * An item of a sitemap.
 *
 * @author Michael Hamann
 */
class SitemapItem {
    public $url;
    public $lastmod;
    public $changefreq;
    public $priority;

    /**
     * Create a new item.
     *
     * @param string $url        The url of the item
     * @param int    $lastmod    Timestamp of the last modification
     * @param string $changefreq How frequently the item is likely to change. Valid values: always, hourly, daily, weekly, monthly, yearly, never.
     * @param $priority float|string The priority of the item relative to other URLs on your site. Valid values range from 0.0 to 1.0.
     */
    public function __construct($url, $lastmod, $changefreq = null, $priority = null) {
        $this->url = $url;
        $this->lastmod = $lastmod;
        $this->changefreq = $changefreq;
        $this->priority = $priority;
    }

    /**
     * Helper function for creating an item for a wikipage id.
     *
     * @param string       $id         A wikipage id.
     * @param string       $changefreq How frequently the item is likely to change. Valid values: always, hourly, daily, weekly, monthly, yearly, never.
     * @param float|string $priority   The priority of the item relative to other URLs on your site. Valid values     range from 0.0 to 1.0.
     * @return SitemapItem The sitemap item.
     */
    public static function createFromID($id, $changefreq = null, $priority = null) {
        $id = trim($id);
        $date = @filemtime(wikiFN($id));
        if(!$date) return null;
        return new SitemapItem(wl($id, '', true), $date, $changefreq, $priority);
    }

    /**
     * Get the XML representation of the sitemap item.
     *
     * @return string The XML representation.
     */
    public function toXML() {
        $result = '  <url>'.NL
                 .'    <loc>'.hsc($this->url).'</loc>'.NL
                 .'    <lastmod>'.date_iso8601($this->lastmod).'</lastmod>'.NL;
        if ($this->changefreq !== null)
            $result .= '    <changefreq>'.hsc($this->changefreq).'</changefreq>'.NL;
        if ($this->priority !== null)
            $result .= '    <priority>'.hsc($this->priority).'</priority>'.NL;
        $result .= '  </url>'.NL;
        return $result;
    }
}
