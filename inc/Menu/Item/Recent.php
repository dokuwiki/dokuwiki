<?php

namespace dokuwiki\Menu\Item;

class Recent extends AbstractItem {

    /** @inheritdoc */
    public function __construct() {
        parent::__construct();

        $this->category = 'site';
        $this->accesskey = 'r';
        $this->svg = DOKU_INC . 'lib/images/menu/calendar-clock.svg';
    }

}
