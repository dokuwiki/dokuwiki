<?php

namespace easywiki\Menu\Item;

/**
 * Class Resendpwd
 *
 * Access the "forgot password" dialog
 */
class Resendpwd extends AbstractItem
{
    /** @inheritdoc */
    public function __construct()
    {
        global $INPUT;
        parent::__construct();

        if ($INPUT->server->str('REMOTE_USER')) {
            throw new \RuntimeException("no resendpwd when already logged in");
        }

        $this->svg = WIKI_INC . 'lib/images/menu/lock-reset.svg';
    }
}
