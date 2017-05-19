<?php

namespace dokuwiki\Menu\Item;

class Register extends AbstractItem {

    /** @inheritdoc */
    public function __construct() {
        global $INPUT;
        parent::__construct();

        $this->category = 'user';

        if($INPUT->server->str('REMOTE_USER')) {
            throw new \RuntimeException("no register when already logged in");
        }
    }

}
