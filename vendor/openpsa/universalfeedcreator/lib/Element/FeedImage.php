<?php

/**
 * A FeedImage may be added to a FeedCreator feed.
 *
 * @author  Kai Blankenhorn <kaib@bitfolge.de>
 * @since   1.3
 * @package de.bitfolge.feedcreator
 */
class FeedImage extends HtmlDescribable
{
    /**
     * Mandatory attributes of an image.
     */
    public $title, $url, $link;

    /**
     * Optional attributes of an image.
     */
    public $width, $height, $description;
}
