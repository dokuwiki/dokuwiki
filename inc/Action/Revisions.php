<?php

namespace dokuwiki\Action;

/**
 * Class Revisions
 *
 * Show the list of old revisions of the current page
 *
 * @package dokuwiki\Action
 */
class Revisions extends AbstractAction {

    /** @inheritdoc */
    public function minimumPermission() {
        return AUTH_READ;
    }

    /** @inheritdoc */
    public function tplContent() {
        global $INPUT;
        html_revisions($INPUT->int('first'));
    }
}
