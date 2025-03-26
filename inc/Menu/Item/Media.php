<?php

namespace dokuwiki\Menu\Item;

use dokuwiki\File\StaticImage;

/**
 * Class Media
 *
 * Opens the media manager
 */
class Media extends AbstractItem
{
    /** @inheritdoc */
    public function __construct()
    {
        global $ID;
        parent::__construct();

        $this->svg = StaticImage::path('menu/folder-multiple-image.svg');
        $this->params['ns'] = getNS($ID);
    }
}
