<?php

namespace dokuwiki\Menu\Item;

/**
 * Class Index
 *
 * Shows the sitemap
 */
class Index extends AbstractItem {

    protected $svg = DOKU_INC . 'lib/images/menu/file-tree.svg';

    /** @inheritdoc */
    public function __construct() {
        global $conf;
        global $ID;
        parent::__construct();

        $this->accesskey = 'x';

        // allow searchbots to get to the sitemap from the homepage (when dokuwiki isn't providing a sitemap.xml)
        if($conf['start'] == $ID && !$conf['sitemap']) {
            $this->nofollow = false;
        }
    }

}
