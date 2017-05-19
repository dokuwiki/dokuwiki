<?php

namespace dokuwiki\Menu\Item;

class MediaManager extends AbstractItem {

    protected $svg = DOKU_BASE . 'lib/images/menu/11-mediamanager_folder-image.svg';

    /** @inheritdoc */
    public function __construct() {
        parent::__construct();

        // View image in media manager
        global $IMG;
        $imgNS = getNS($IMG);
        $authNS = auth_quickaclcheck("$imgNS:*");
        if($authNS < AUTH_UPLOAD) {
            throw new \RuntimeException("media manager link only with upload permissions");
        }
        $this->params = array(
            'ns' => $imgNS,
            'image' => $IMG,
            'do' => 'media'
        );
    }

}
