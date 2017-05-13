<?php

namespace dokuwiki\Action;

/**
 * Class Recent
 *
 * The recent changes view
 *
 * @package dokuwiki\Action
 */
class Recent extends AbstractAction {

    /** @inheritdoc */
    public function minimumPermission() {
        return AUTH_NONE;
    }

    /** @inheritdoc */
    public function preProcess() {
        global $INPUT;
        $show_changes = $INPUT->str('show_changes');
        if(!empty($show_changes)) {
            set_doku_pref('show_changes', $show_changes);
        }
    }

    /** @inheritdoc */
    public function tplContent() {
        global $INPUT;
        html_recent((int) $INPUT->extract('first')->int('first'));
    }

}
