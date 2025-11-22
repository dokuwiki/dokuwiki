<?php

namespace easywiki\Menu\Item;

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
        $this->svg = WIKI_INC . 'lib/images/menu/calendar-clock.svg';
    }
}
