<?php

namespace dokuwiki\Menu\Item;

/**
 * Class Recent
 *
 * Show the site wide recent changes
 */
class Recent extends AbstractItem {

    /** @inheritdoc */
    public function __construct() {
        parent::__construct();

        $this->accesskey = 'r';
        $this->svg = DOKU_INC . 'lib/images/menu/calendar-clock.svg';
    }

}
