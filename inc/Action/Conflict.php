<?php
/**
 * Created by IntelliJ IDEA.
 * User: andi
 * Date: 2/11/17
 * Time: 11:52 AM
 */

namespace dokuwiki\Action;

class Conflict extends AbstractAction {

    /** @inheritdoc */
    function minimumPermission() {
        global $INFO;
        if($INFO['exists']) {
            return AUTH_EDIT;
        } else {
            return AUTH_CREATE;
        }
    }

    public function tplContent() {
        global $PRE;
        global $TEXT;
        global $SUF;
        global $SUM;

        html_conflict(con($PRE, $TEXT, $SUF), $SUM);
        html_diff(con($PRE, $TEXT, $SUF), false);
    }

}
