<?php

namespace dokuwiki\Menu\Item;

class Back extends AbstractItem {

    /** @inheritdoc */
    public function __construct() {
        global $ID;
        parent::__construct();

        $parent = tpl_getparent($ID);
        if(!$parent) {
            throw new \RuntimeException("No parent for back action");
        }

        $this->id = $parent;
        $this->params = array('do' => '');
        $this->accesskey = 'b';
    }

}
