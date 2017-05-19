<?php

namespace dokuwiki\Menu\Item;

class Index extends AbstractItem {

    /** @inheritdoc */
    public function __construct() {
        parent::__construct();

        $this->category = 'site';
        $this->accesskey = 'x';

        // allow searchbots to get to the sitemap from the homepage (when dokuwiki isn't providing a sitemap.xml)
        global $conf;
        global $ID;
        if($conf['start'] == $ID && !$conf['sitemap']) {
            $this->nofollow = false;
        }
    }

}
