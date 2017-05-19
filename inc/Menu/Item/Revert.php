<?php

namespace dokuwiki\Menu\Item;

class Revert extends AbstractItem {

    /** @inheritdoc */
    public function __construct() {
        global $REV;
        global $INFO;
        parent::__construct();

        if(!$INFO['ismanager'] || !$REV || !$INFO['writable']) {
            throw new \RuntimeException('revert not available');
        }
        $this->params['rev'] = $REV;
        $this->params['sectok'] = getSecurityToken();
        $this->svg = DOKU_BASE . 'lib/images/menu/06-revert_replay.svg';
    }

}
