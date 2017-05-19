<?php

namespace dokuwiki\Menu\Item;

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
