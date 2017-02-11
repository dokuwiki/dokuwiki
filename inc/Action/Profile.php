<?php
/**
 * Created by IntelliJ IDEA.
 * User: andi
 * Date: 2/11/17
 * Time: 9:47 AM
 */

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionAbort;

class Profile extends AbstractUserAction {

    /** @inheritdoc */
    function minimumPermission() {
        return AUTH_NONE;
    }

    public function preProcess() {
        global $lang;
        if(updateprofile()) {
            msg($lang['profchanged'], 1);
            throw new ActionAbort('show');
        }
    }

    public function tplContent() {
        html_updateprofile();
    }

}
