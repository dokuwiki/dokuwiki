<?php

namespace dokuwiki\Menu\Item;

/**
 * Class ImgBackto
 *
 * Links back to the originating page from a detail image view
 */
class ImgBackto extends AbstractItem {

    /** @inheritdoc */
    public function __construct() {
        global $ID;
        parent::__construct();

        $this->svg = DOKU_INC . 'lib/images/menu/12-back_arrow-left.svg';
        $this->type = 'img_backto';
        $this->params = array();
        $this->accesskey = 'b';
        $this->replacement = $ID;
    }

}
