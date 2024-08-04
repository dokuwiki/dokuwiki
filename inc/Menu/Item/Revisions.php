<?php

namespace dokuwiki\Menu\Item;

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
        global $INFO;
        parent::__construct();
        if (auth_quickaclcheck($INFO['id']) < AUTH_HISTORY) throw new \RuntimeException("no permission to see revisions");

        $this->accesskey = 'o';
        $this->type = 'revs';
        $this->svg = DOKU_INC . 'lib/images/menu/07-revisions_history.svg';
    }
}
