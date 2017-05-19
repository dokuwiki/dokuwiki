<?php

namespace dokuwiki\Menu\Item;

class Admin extends AbstractItem {

    /** @inheritdoc */
    public function __construct() {
        global $INFO;
        parent::__construct();

        $this->category = 'user';

        if(!$INFO['ismanager']) {
            throw new \RuntimeException("admin is for managers only");
        }
    }

}
