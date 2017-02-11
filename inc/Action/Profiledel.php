<?php
/**
 * Created by IntelliJ IDEA.
 * User: andi
 * Date: 2/11/17
 * Time: 9:47 AM
 */

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionAbort;

/**
 * Class Profiledel
 * @package dokuwiki\Action
 * @fixme rename profile_delete action to profiledel
 */
class Profiledel extends AbstractUserAction {

    /** @inheritdoc */
    function minimumPermission() {
        return AUTH_NONE;
    }

    public function preProcess() {
        global $lang;
        if(auth_deleteprofile()){
            msg($lang['profdeleted'],1);
            throw new ActionAbort('show');
        } else {
            throw new ActionAbort('profile');
        }
    }

}
