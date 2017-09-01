<?php

namespace dokuwiki\Action;

/**
 * Class Backlink
 *
 * Shows which pages link to the current page
 *
 * @package dokuwiki\Action
 */
class Backlink extends AbstractAction {

    /** @inheritdoc */
    public function minimumPermission() {
        return AUTH_NONE;
    }

    /** @inheritdoc */
    public function tplContent() {
        html_backlinks();
    }

}
