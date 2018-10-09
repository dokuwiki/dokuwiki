<?php

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionDisabledException;
use dokuwiki\Action\Exception\ActionException;

/**
 * Class Logout
 *
 * Log out a user
 *
 * @package dokuwiki\Action
 */
class Logout extends AbstractUserAction {

    /** @inheritdoc */
    public function minimumPermission() {
        return AUTH_NONE;
    }

    /** @inheritdoc */
    public function checkPreconditions() {
        parent::checkPreconditions();

        /** @var \DokuWiki_Auth_Plugin $auth */
        global $auth;
        if(!$auth->canDo('logout')) throw new ActionDisabledException();
    }

    /** @inheritdoc */
    public function preProcess() {
        global $ID;
        global $INPUT;

        // when logging out during an edit session, unlock the page
        $lockedby = checklock($ID);
        if($lockedby == $INPUT->server->str('REMOTE_USER')) {
            unlock($ID);
        }

        // do the logout stuff and redirect to login
        auth_logoff();
        send_redirect(wl($ID, array('do' => 'login')));

        // should never be reached
        throw new ActionException('login');
    }

}
