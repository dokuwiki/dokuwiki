<?php

/**
 * GPXCreator is a FeedCreator that implements a GPX output, suitable for a GIS packages
 *
 * @since   1.7.6
 * @author  Barry Hunter <geo@barryhunter.co.uk>
 */
class GPXCreator extends FeedCreator
{

    /**
     * GPXCreator constructor.
     */
    public function __construct()
    {
        $this->contentType = "text/xml";
        $this->encoding = "utf-8";
    }

    /** @inheritdoc */
    public function createFeed()
    {
        $feed = "<?xml version=\"1.0\" encoding=\"".$this->encoding."\"?>\n";
        $feed .= $this->_createStylesheetReferences();
        $feed .= "<gpx xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" version=\"1.0\"
        creator=\"".FEEDCREATOR_VERSION."\"
        xsi:schemaLocation=\"http://www.topografix.com/GPX/1/0 http://www.topografix.com/GPX/1/0/gpx.xsd\" xmlns=\"http://www.topografix.com/GPX/1/0\">\n";

        $now = new FeedDate();
        $feed .= "<desc>".FeedCreator::iTrunc(htmlspecialchars($this->title), 100)."</desc>
        <author>{$http_host}</author>
        <url>".htmlspecialchars($this->link)."</url>
        <time>".htmlspecialchars($now->iso8601())."</time>
        \n";

        for ($i = 0; $i < count($this->items); $i++) {
            $feed .= "<wpt lat=\"".$this->items[$i]->lat."\" lon=\"".$this->items[$i]->long."\">
            <name>".substr(htmlspecialchars(strip_tags($this->items[$i]->title)), 0, 6)."</name>
                <desc>".htmlspecialchars(strip_tags($this->items[$i]->title))."</desc>
                    <src>".htmlspecialchars($this->items[$i]->author)."</src>
                        <url>".htmlspecialchars($this->items[$i]->link)."</url>
        </wpt>\n";
        }
        $feed .= "</gpx>\n";

        return $feed;
    }
}
