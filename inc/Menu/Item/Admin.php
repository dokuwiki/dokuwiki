<?php

namespace dokuwiki\Menu\Item;

/**
 * Class Admin
 *
 * Opens the Admin screen. Only shown to managers or above
 */
class Admin extends AbstractItem
{
    /** @inheritdoc */
    public function __construct()
    {
        global $INPUT;
        global $INFO;

        parent::__construct();

        if (!$INPUT->server->str('REMOTE_USER')) {
            throw new \RuntimeException("admin is only for logged in users");
        }

        if (!isset($INFO) || !$INFO['ismanager']) {
            throw new \RuntimeException("admin is only for managers and above");
        }

        $this->svg = DOKU_INC . 'lib/images/menu/settings.svg';
    }
}
