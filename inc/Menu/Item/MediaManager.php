<?php

namespace dokuwiki\Menu\Item;

/**
 * Class MediaManager
 *
 * Opens the current image in the media manager. Used on image detail view.
 */
class MediaManager extends AbstractItem {

    protected $svg = DOKU_INC . 'lib/images/menu/11-mediamanager_folder-image.svg';

    /** @inheritdoc */
    public function __construct() {
        global $IMG;
        parent::__construct();

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
