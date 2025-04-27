<?php

namespace dokuwiki\Menu\Item;

use dokuwiki\File\StaticImage;

/**
 * Class Index
 *
 * Shows the sitemap
 */
class Index extends AbstractItem
{
    /** @inheritdoc */
    public function __construct()
    {
        global $conf;
        global $ID;
        parent::__construct();

        $this->accesskey = 'x';
        $this->svg = StaticImage::path('menu/file-tree.svg');

        // allow searchbots to get to the sitemap from the homepage (when dokuwiki isn't providing a sitemap.xml)
        if ($conf['start'] == $ID && !$conf['sitemap']) {
            $this->nofollow = false;
        }
    }
}
