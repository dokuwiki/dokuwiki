<?php

namespace dokuwiki\Menu\Item;

use dokuwiki\File\StaticImage;

/**
 * Class Backlink
 *
 * Shows the backlinks for the current page
 */
class Backlink extends AbstractItem
{
    /** @inheritdoc */
    public function __construct()
    {
        parent::__construct();
        $this->svg = StaticImage::path('menu/08-backlink_link-variant.svg');
    }
}
