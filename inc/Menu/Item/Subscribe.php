<?php

namespace dokuwiki\Menu\Item;

class Subscribe extends AbstractItem {

    protected $svg = DOKU_BASE . 'lib/images/menu/09-subscribe_email-outline.svg';

    /** @inheritdoc */
    public function __construct() {
        global $INPUT;
        parent::__construct();

        if(!$INPUT->server->str('REMOTE_USER')) {
            throw new \RuntimeException("subscribe is only for logged in users");
        }
    }

}
