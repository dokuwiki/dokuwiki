<?php
/**
 * Created by IntelliJ IDEA.
 * User: andi
 * Date: 2/10/17
 * Time: 12:08 PM
 */

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionException;
use dokuwiki\Action\Exception\ActionNoUserException;

class Logout extends AbstractAclAction {

    /** @inheritdoc */
    function minimumPermission() {
        return AUTH_NONE;
    }

    /** @inheritdoc */
    public function checkPermissions() {
        global $INPUT;
        parent::checkPermissions();
        if(!$INPUT->server->has('REMOTE_USER')) {
            throw new ActionNoUserException('login');
        }
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
