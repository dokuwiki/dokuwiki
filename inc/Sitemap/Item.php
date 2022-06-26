<?php

namespace dokuwiki\Sitemap;

/**
 * An item of a sitemap.
 *
 * @author Michael Hamann
 */
class Item {
    public $url;
    public $lastmod;
    public $changefreq;
    public $priority;

    /**
     * Create a new item.
     *
     * @param string $url        The url of the item
     * @param int    $lastmod    Timestamp of the last modification
     * @param string $changefreq How frequently the item is likely to change.
     *                           Valid values: always, hourly, daily, weekly, monthly, yearly, never.
     * @param $priority float|string The priority of the item relative to other URLs on your site.
     *                           Valid values range from 0.0 to 1.0.
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
     * @param string       $changefreq How frequently the item is likely to change.
     *                                 Valid values: always, hourly, daily, weekly, monthly, yearly, never.
     * @param float|string $priority   The priority of the item relative to other URLs on your site.
     *                                 Valid values range from 0.0 to 1.0.
     * @return Item The sitemap item.
     */
    public static function createFromID($id, $changefreq = null, $priority = null) {
        $id = trim($id);
        $date = @filemtime(wikiFN($id));
        if(!$date) return null;
        return new Item(wl($id, '', true), $date, $changefreq, $priority);
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
