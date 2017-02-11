<?php

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionAbort;

/**
 * Class Profile
 *
 * Handle the profile form
 *
 * @package dokuwiki\Action
 */
class Profile extends AbstractUserAction {

    /** @inheritdoc */
    function minimumPermission() {
        return AUTH_NONE;
    }

    /** @inheritdoc */
    public function preProcess() {
        global $lang;
        if(updateprofile()) {
            msg($lang['profchanged'], 1);
            throw new ActionAbort('show');
        }
    }

    /** @inheritdoc */
    public function tplContent() {
        html_updateprofile();
    }

}
