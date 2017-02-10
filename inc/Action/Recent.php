<?php
/**
 * Created by IntelliJ IDEA.
 * User: andi
 * Date: 2/10/17
 * Time: 3:21 PM
 */

namespace dokuwiki\Action;

class Recent extends AbstractAction {

    /** @inheritdoc */
    function minimumPermission() {
        return AUTH_NONE;
    }

    /** @inheritdoc */
    public function preProcess() {
        global $INPUT;
        $show_changes = $INPUT->str('show_changes');
        if (!empty($show_changes)) {
            set_doku_pref('show_changes', $show_changes);
        }
    }

    public function tplContent() {
        global $INPUT;
        html_recent($INPUT->int('first'));
    }

}
