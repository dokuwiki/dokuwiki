<?php

namespace dokuwiki\Menu\Item;

/**
 * Class Register
 *
 * Open the view to register a new account
 */
class Register extends AbstractItem {

    /** @inheritdoc */
    public function __construct() {
        global $INPUT;
        parent::__construct();

        if($INPUT->server->str('REMOTE_USER')) {
            throw new \RuntimeException("no register when already logged in");
        }

        $this->svg = DOKU_INC . 'lib/images/menu/account-plus.svg';
    }

}
