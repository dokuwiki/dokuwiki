<?php

namespace dokuwiki\Menu\Item;

class Admin extends AbstractItem {

    protected $svg = DOKU_INC . 'lib/images/menu/settings.svg';

    /** @inheritdoc */
    public function __construct() {
        global $INFO;
        parent::__construct();

        if(!$INFO['ismanager']) {
            throw new \RuntimeException("admin is for managers only");
        }
    }

}
