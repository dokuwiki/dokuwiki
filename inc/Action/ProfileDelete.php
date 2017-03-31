<?php

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionAbort;

/**
 * Class ProfileDelete
 *
 * Delete a user account
 *
 * @package dokuwiki\Action
 */
class ProfileDelete extends AbstractUserAction {

    /** @inheritdoc */
    public function minimumPermission() {
        return AUTH_NONE;
    }

    /** @inheritdoc */
    public function preProcess() {
        global $lang;
        if(auth_deleteprofile()) {
            msg($lang['profdeleted'], 1);
            throw new ActionAbort('show');
        } else {
            throw new ActionAbort('profile');
        }
    }

}
