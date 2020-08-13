<?php

namespace dokuwiki\Menu\Item;

/**
 * Class Media
 *
 * Opens the media manager
 */
class Media extends AbstractItem {

    /** @inheritdoc */
    public function __construct() {
        global $ID;
        parent::__construct();

        $this->svg = DOKU_INC . 'lib/images/menu/folder-multiple-image.svg';
        $this->params['ns'] = getNS($ID);
    }

}
