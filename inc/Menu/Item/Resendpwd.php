<?php

namespace dokuwiki\Menu\Item;

class Resendpwd extends AbstractItem {

    /** @inheritdoc */
    public function __construct() {
        global $INPUT;
        parent::__construct();

        $this->category = 'user';

        if($INPUT->server->str('REMOTE_USER')) {
            throw new \RuntimeException("no resendpwd when already logged in");
        }
    }

}
