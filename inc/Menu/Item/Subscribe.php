<?php

namespace dokuwiki\Menu\Item;

use dokuwiki\File\StaticImage;

/**
 * Class Subscribe
 *
 * Access the subscription management view
 */
class Subscribe extends AbstractItem
{
    /** @inheritdoc */
    public function __construct()
    {
        global $INPUT;
        parent::__construct();

        if (!$INPUT->server->str('REMOTE_USER')) {
            throw new \RuntimeException("subscribe is only for logged in users");
        }

        $this->svg = StaticImage::path('menu/09-subscribe_email-outline.svg');
    }
}
