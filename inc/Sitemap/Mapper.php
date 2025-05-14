<?php

/**
 * Sitemap handling functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michael Hamann <michael@content-space.de>
 */

namespace dokuwiki\Sitemap;

use dokuwiki\Extension\Event;
use dokuwiki\HTTP\DokuHTTPClient;
use dokuwiki\Logger;

/**
 * A class for building sitemaps and pinging search engines with the sitemap URL.
 *
 * @author Michael Hamann
 */
class Mapper
{
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
    public static function generate()
    {
        global $conf;
        if ($conf['sitemap'] < 1 || !is_numeric($conf['sitemap'])) return false;

        $sitemap = Mapper::getFilePath();

        if (file_exists($sitemap)) {
            if (!is_writable($sitemap)) return false;
        } elseif (!is_writable(dirname($sitemap))) {
            return false;
        }

        if (
            @filesize($sitemap) &&
            @filemtime($sitemap) > (time() - ($conf['sitemap'] * 86400))
        ) { // 60*60*24=86400
            Logger::debug('Sitemapper::generate(): Sitemap up to date');
            return false;
        }

        Logger::debug("Sitemapper::generate(): using $sitemap");

        $pages = idx_get_indexer()->getPages();
        Logger::debug('Sitemapper::generate(): creating sitemap using ' . count($pages) . ' pages');
        $items = [];

        // build the sitemap items
        foreach ($pages as $id) {
            //skip hidden, non existing and restricted files
            if (isHiddenPage($id)) continue;
            if (auth_aclcheck($id, '', []) < AUTH_READ) continue;
            $item = Item::createFromID($id);
            if ($item instanceof Item)
                $items[] = $item;
        }

        $eventData = ['items' => &$items, 'sitemap' => &$sitemap];
        $event = new Event('SITEMAP_GENERATE', $eventData);
        if ($event->advise_before(true)) {
            //save the new sitemap
            $event->result = io_saveFile($sitemap, (new Mapper())->getXML($items));
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
    private function getXML($items)
    {
        ob_start();
        echo '<?xml version="1.0" encoding="UTF-8"?>' . NL;
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . NL;
        foreach ($items as $item) {
            /** @var Item $item */
            echo $item->toXML();
        }
        echo '</urlset>' . NL;
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
    public static function getFilePath()
    {
        global $conf;

        $sitemap = $conf['cachedir'] . '/sitemap.xml';
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
    public static function sitemapIsCompressed()
    {
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
    public static function pingSearchEngines()
    {
        //ping search engines...
        $http = new DokuHTTPClient();
        $http->timeout = 8;

        $encoded_sitemap_url = urlencode(wl('', ['do' => 'sitemap'], true, '&'));
        $ping_urls = [
            'google'    => 'https://www.google.com/ping?sitemap=' . $encoded_sitemap_url,
            'yandex'    => 'https://webmaster.yandex.com/ping?sitemap=' . $encoded_sitemap_url
        ];

        $data = [
            'ping_urls' => $ping_urls,
            'encoded_sitemap_url' => $encoded_sitemap_url
        ];
        $event = new Event('SITEMAP_PING', $data);
        if ($event->advise_before(true)) {
            foreach ($data['ping_urls'] as $name => $url) {
                Logger::debug("Sitemapper::PingSearchEngines(): pinging $name");
                $resp = $http->get($url);
                if ($http->error) {
                    Logger::debug("Sitemapper:pingSearchengines(): $http->error", $resp);
                }
            }
        }
        $event->advise_after();

        return true;
    }
}
