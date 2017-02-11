<?php

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionAbort;

/**
 * Class Profiledel
 *
 * Delete a user account
 *
 * @package dokuwiki\Action
 * @fixme rename profile_delete action to profiledel
 */
class Profiledel extends AbstractUserAction {

    /** @inheritdoc */
    function minimumPermission() {
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
