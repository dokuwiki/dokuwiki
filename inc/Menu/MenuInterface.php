<?php

namespace dokuwiki\Menu;

use dokuwiki\Menu\Item\AbstractItem;

/**
 * Interface MenuInterface
 *
 * Defines what a Menu provides
 */
interface MenuInterface
{
    /**
     * Get the list of action items in this menu
     *
     * @return AbstractItem[]
     */
    public function getItems();
}
