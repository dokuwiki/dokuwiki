<?php

namespace dokuwiki\Action;

/**
 * Class Locked
 *
 * Show a locked screen when a page is locked
 *
 * @package dokuwiki\Action
 */
class Locked extends AbstractAction {

    /** @inheritdoc */
    public function minimumPermission() {
        return AUTH_READ;
    }

    /** @inheritdoc */
    public function tplContent() {
        html_locked();
        html_edit();
    }

}
