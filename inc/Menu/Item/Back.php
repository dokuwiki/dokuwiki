<?php

namespace dokuwiki\Menu\Item;

/**
 * Class Back
 *
 * Navigates back up one namepspace. This is currently not used in any menu. Templates
 * would need to add this item manually.
 */
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
        $this->svg = DOKU_INC . 'lib/images/menu/12-back_arrow-left.svg';
    }

}
