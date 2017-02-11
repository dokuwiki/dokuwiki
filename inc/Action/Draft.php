<?php
/**
 * Created by IntelliJ IDEA.
 * User: andi
 * Date: 2/11/17
 * Time: 11:55 AM
 */

namespace dokuwiki\Action;

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
