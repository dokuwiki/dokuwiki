<?php

namespace dokuwiki\Menu\Item;

use dokuwiki\File\StaticImage;

/**
 * Class Revisions
 *
 * Access the old revisions of the current page
 */
class Revisions extends AbstractItem
{
    /** @inheritdoc */
    public function __construct()
    {
        parent::__construct();

        $this->accesskey = 'o';
        $this->type = 'revs';
        $this->svg = StaticImage::path('menu/07-revisions_history.svg');
    }
}
