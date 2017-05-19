<?php

namespace dokuwiki\Menu\Item;

class Media extends AbstractItem {

    protected $svg = DOKU_INC . 'lib/images/menu/folder-multiple-image.svg';

    /** @inheritdoc */
    public function __construct() {
        global $ID;
        parent::__construct();

        $params['ns'] = getNS($ID);
    }

}
