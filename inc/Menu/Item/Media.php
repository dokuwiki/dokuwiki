<?php

namespace easywiki\Menu\Item;

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

        $this->svg = WIKI_INC . 'lib/images/menu/folder-multiple-image.svg';
        $this->params['ns'] = getNS($ID);
    }
}
