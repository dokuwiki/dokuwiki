<?php

namespace dokuwiki\Menu\Item;

class Profile extends AbstractItem {

    /** @inheritdoc */
    public function __construct() {
        global $INPUT;
        parent::__construct();

        if(!$INPUT->server->str('REMOTE_USER')) {
            throw new \RuntimeException("profile is only for logged in users");
        }
    }

}
