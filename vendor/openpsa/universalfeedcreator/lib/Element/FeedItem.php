<?php

/**
 * A FeedItem is a part of a FeedCreator feed.
 *
 * @author  Kai Blankenhorn <kaib@bitfolge.de>
 * @since   1.3
 */
class FeedItem extends HtmlDescribable
{
    /**
     * Mandatory attributes of an item.
     */
    public $title, $description, $link;

    /**
     * Optional attributes of an item.
     */
    public $author, $authorEmail, $authorURL, $image, $category, $categoryScheme, $comments, $guid, $source, $creator, $contributor, $lat, $long, $thumb;

    /**
     * Publishing date of an item. May be in one of the following formats:
     *    RFC 822:
     *    "Mon, 20 Jan 03 18:05:41 +0400"
     *    "20 Jan 03 18:05:41 +0000"
     *    ISO 8601:
     *    "2003-01-20T18:05:41+04:00"
     *    Unix:
     *    1043082341
     */
    public $date;

    /**
     * Add <enclosure> element tag RSS 2.0, supported by ATOM 1.0 too
     * modified by : Mohammad Hafiz bin Ismail (mypapit@gmail.com)
     * display :
     * <enclosure length="17691" url="http://something.com/picture.jpg" type="image/jpeg" />
     */
    public $enclosure;

    /**
     * Any additional elements to include as an associated array. All $key => $value pairs
     * will be included unencoded in the feed item in the form
     *     <$key>$value</$key>
     * Again: No encoding will be used! This means you can invalidate or enhance the feed
     * if $value contains markup. This may be abused to embed tags not implemented by
     * the FeedCreator class used.
     */
    public $additionalElements = Array();

    // on hold
    // var $source;
}
