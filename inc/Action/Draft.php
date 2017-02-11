<?php

namespace dokuwiki\Action;

/**
 * Class Draft
 *
 * Screen to see and recover a draft
 *
 * @package dokuwiki\Action
 * @fixme combine with Recover?
 */
class Draft extends AbstractAction {

    /** @inheritdoc */
    function minimumPermission() {
        global $INFO;
        if($INFO['exists']) {
            return AUTH_EDIT;
        } else {
            return AUTH_CREATE;
        }
    }

    // FIXME any permission checks needed?

    public function tplContent() {
        html_draft();
    }

}
