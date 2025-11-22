<?php

namespace easywiki\Menu\Item;

/**
 * Class Backlink
 *
 * Shows the backlinks for the current page
 */
class Backlink extends AbstractItem
{
    /** @inheritdoc */
    public function __construct()
    {
        parent::__construct();
        $this->svg = WIKI_INC . 'lib/images/menu/08-backlink_link-variant.svg';
    }
}
