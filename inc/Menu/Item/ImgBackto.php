<?php

namespace dokuwiki\Menu\Item;

use dokuwiki\File\StaticImage;

/**
 * Class ImgBackto
 *
 * Links back to the originating page from a detail image view
 */
class ImgBackto extends AbstractItem
{
    /** @inheritdoc */
    public function __construct()
    {
        global $ID;
        parent::__construct();

        $this->svg = StaticImage::path('menu/12-back_arrow-left.svg');
        $this->type = 'img_backto';
        $this->params = [];
        $this->accesskey = 'b';
        $this->replacement = $ID;
    }
}
