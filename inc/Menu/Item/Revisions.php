<?php

namespace dokuwiki\Menu\Item;

class Revisions extends AbstractItem {

    /** @inheritdoc */
    public function __construct() {
        parent::__construct();

        $this->type = 'revs';
        $this->params['do'] = 'revs';
        $this->svg = DOKU_INC . 'lib/images/menu/07-revisions_history.svg';
    }

}
