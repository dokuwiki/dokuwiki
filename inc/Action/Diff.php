<?php
/**
 * Created by IntelliJ IDEA.
 * User: andi
 * Date: 2/10/17
 * Time: 3:30 PM
 */

namespace dokuwiki\Action;

class Diff extends AbstractAction {

    /** @inheritdoc */
    function minimumPermission() {
        return AUTH_READ;
    }

    public function preProcess() {
        global $INPUT;

        // store the selected diff type in cookie
        $difftype = $INPUT->str('difftype');
        if (!empty($difftype)) {
            set_doku_pref('difftype', $difftype);
        }
    }

}
