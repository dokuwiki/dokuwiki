<?php

namespace dokuwiki\Menu\Item;

class Media extends AbstractItem {

    /** @inheritdoc */
    public function __construct() {
        global $ID;
        parent::__construct();

        $this->category = 'site';
        $params['ns'] = getNS($ID);
    }

}
