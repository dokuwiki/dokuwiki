<?php

namespace dokuwiki\Action;

/**
 * Class Media
 *
 * The full screen media manager
 *
 * @package dokuwiki\Action
 */
class Media extends AbstractAction {

    /** @inheritdoc */
    public function minimumPermission() {
        return AUTH_READ;
    }

    /** @inheritdoc */
    public function tplContent() {
        tpl_media();
    }

}
