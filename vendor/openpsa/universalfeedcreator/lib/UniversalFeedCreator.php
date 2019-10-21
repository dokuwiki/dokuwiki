<?php
/**
 * UniversalFeedCreator lets you choose during runtime which
 * format to build.
 * For general usage of a feed class, see the FeedCreator class
 * below or the example above.
 *
 * @since   1.3
 * @author  Kai Blankenhorn <kaib@bitfolge.de>
 */
class UniversalFeedCreator extends FeedCreator
{
    /** @var FeedCreator */
    protected $_feed;

    /**
     * @param string $format
     */
    protected function _setFormat($format)
    {
        switch (strtoupper($format)) {

            case "BASE":
                $this->format = $format;
            case "2.0":
                // fall through
            case "RSS2.0":
                $this->_feed = new RSSCreator20();
                break;

            case "GEOPHOTORSS":
            case "PHOTORSS":
            case "GEORSS":
                $this->format = $format;
            case "1.0":
                // fall through
            case "RSS1.0":
                $this->_feed = new RSSCreator10();
                break;

            case "0.91":
                // fall through
            case "RSS0.91":
                $this->_feed = new RSSCreator091();
                break;

            case "PIE0.1":
                $this->_feed = new PIECreator01();
                break;

            case "MBOX":
                $this->_feed = new MBOXCreator();
                break;

            case "OPML":
                $this->_feed = new OPMLCreator();
                break;

            case "TOOLBAR":
                $this->format = $format;

            case "ATOM":
                // fall through: always the latest ATOM version
            case "ATOM1.0":
                $this->_feed = new AtomCreator10();
                break;

            case "ATOM0.3":
                $this->_feed = new AtomCreator03();
                break;

            case "HTML":
                $this->_feed = new HTMLCreator();
                break;

            case "PHP":
                $this->_feed = new PHPCreator();
                break;
            case "GPX":
                $this->_feed = new GPXCreator();
                break;
            case "KML":
                $this->_feed = new KMLCreator();
                break;
            case "JS":
                // fall through
            case "JAVASCRIPT":
                $this->_feed = new JSCreator();
                break;

            default:
                $this->_feed = new RSSCreator091();
                break;
        }

        $vars = get_object_vars($this);
        foreach ($vars as $key => $value) {
            // prevent overwriting of properties "contentType", "encoding"; do not copy "_feed" itself
            if (!in_array($key, array("_feed", "contentType", "encoding"))) {
                $this->_feed->{$key} = $this->{$key};
            }
        }
    }

    /**
     * Creates a syndication feed based on the items previously added.
     *
     * @see FeedCreator::addItem()
     * @param string $format format the feed should comply to. Valid values are:
     *                       "PIE0.1", "mbox", "RSS0.91", "RSS1.0", "RSS2.0", "OPML", "ATOM0.3", "HTML", "JS"
     * @return string the contents of the feed.
     */
    public function createFeed($format = "RSS0.91")
    {
        $this->_setFormat($format);

        return $this->_feed->createFeed();
    }

    /**
     * Saves this feed as a file on the local disk. After the file is saved, an HTTP redirect
     * header may be sent to redirect the use to the newly created file.
     *
     * @since 1.4
     * @param string $format           format the feed should comply to. Valid values are:
     *                                 "PIE0.1" (deprecated), "mbox", "RSS0.91", "RSS1.0", "RSS2.0", "OPML", "ATOM",
     *                                 "ATOM0.3", "HTML", "JS"
     * @param string $filename         optional    the filename where a recent version of the feed is saved. If not
     *                                 specified, the filename is $_SERVER["PHP_SELF"] with the extension changed to
     *                                 .xml (see _generateFilename()).
     * @param boolean $displayContents optional    send the content of the file or not. If true, the file will be sent
     *                                 in the body of the response.
     */
    public function saveFeed($format = "RSS0.91", $filename = "", $displayContents = true)
    {
        $this->_setFormat($format);
        $this->_feed->saveFeed($filename, $displayContents);
    }

    /**
     * Turns on caching and checks if there is a recent version of this feed in the cache.
     * If there is, an HTTP redirect header is sent.
     * To effectively use caching, you should create the FeedCreator object and call this method
     * before anything else, especially before you do the time consuming task to build the feed
     * (web fetching, for example).
     *
     * @param string $format   format the feed should comply to. Valid values are:
     *                         "PIE0.1" (deprecated), "mbox", "RSS0.91", "RSS1.0", "RSS2.0", "OPML", "ATOM0.3".
     * @param string $filename optional the filename where a recent version of the feed is saved. If not specified, the
     *                         filename is $_SERVER["PHP_SELF"] with the extension changed to .xml (see
     *                         _generateFilename()).
     * @param int $timeout     optional the timeout in seconds before a cached version is refreshed (defaults to 3600 =
     *                         1 hour)
     */
    public function useCached($format = "RSS0.91", $filename = "", $timeout = 3600)
    {
        $this->_setFormat($format);
        $this->_feed->useCached($filename, $timeout);
    }
}
