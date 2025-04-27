<?php

namespace dokuwiki\Menu\Item;

use dokuwiki\File\StaticImage;

/**
 * Class Register
 *
 * Open the view to register a new account
 */
class Register extends AbstractItem
{
    /** @inheritdoc */
    public function __construct()
    {
        global $INPUT;
        parent::__construct();

        if ($INPUT->server->str('REMOTE_USER')) {
            throw new \RuntimeException("no register when already logged in");
        }

        $this->svg = StaticImage::path('menu/account-plus.svg');
    }
}
