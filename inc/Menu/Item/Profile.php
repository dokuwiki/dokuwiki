<?php

namespace dokuwiki\Menu\Item;

use dokuwiki\File\StaticImage;

/**
 * Class Profile
 *
 * Open the user's profile
 */
class Profile extends AbstractItem
{
    /** @inheritdoc */
    public function __construct()
    {
        global $INPUT;
        parent::__construct();

        if (!$INPUT->server->str('REMOTE_USER')) {
            throw new \RuntimeException("profile is only for logged in users");
        }

        $this->svg = StaticImage::path('menu/account-card-details.svg');
    }
}
