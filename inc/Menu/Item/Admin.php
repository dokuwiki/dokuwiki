<?php

namespace dokuwiki\Menu\Item;

use dokuwiki\File\StaticImage;

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
        parent::__construct();

        $this->svg = StaticImage::path('menu/settings.svg');
    }

    /** @inheritdoc */
    public function visibleInContext($ctx)
    {
        global $INFO;
        if (!$INFO['ismanager']) return false;

        return parent::visibleInContext($ctx);
    }
}
