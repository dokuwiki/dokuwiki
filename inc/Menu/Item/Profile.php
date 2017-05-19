<?php

namespace dokuwiki\Menu\Item;

class Profile extends AbstractItem {

    protected $svg = DOKU_INC . 'lib/images/menu/account-card-details.svg';

    /** @inheritdoc */
    public function __construct() {
        global $INPUT;
        parent::__construct();

        if(!$INPUT->server->str('REMOTE_USER')) {
            throw new \RuntimeException("profile is only for logged in users");
        }
    }

}
