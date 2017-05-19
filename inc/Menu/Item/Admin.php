<?php

namespace dokuwiki\Menu\Item;

/**
 * Class Admin
 *
 * Opens the Admin screen. Only shown to managers or above
 */
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
