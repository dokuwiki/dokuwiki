<?php

namespace dokuwiki\Menu\Item;

use dokuwiki\File\StaticImage;

/**
 * Class Recent
 *
 * Show the site wide recent changes
 */
class Recent extends AbstractItem
{
    /** @inheritdoc */
    public function __construct()
    {
        parent::__construct();

        $this->accesskey = 'r';
        $this->svg = StaticImage::path('menu/calendar-clock.svg');
    }
}
