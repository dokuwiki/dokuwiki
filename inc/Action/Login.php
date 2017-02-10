<?php
/**
 * Created by IntelliJ IDEA.
 * User: andi
 * Date: 2/10/17
 * Time: 12:08 PM
 */

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionException;

class Login extends AbstractAclAction {

    /** @inheritdoc */
    function minimumPermission() {
        return AUTH_NONE;
    }

    /** @inheritdoc */
    public function checkPermissions() {
        global $INPUT;
        parent::checkPermissions();
        if($INPUT->server->has('REMOTE_USER')){
            // nothing to do
            throw new ActionException();
        }
        // FIXME auth login capabilities
    }

    /** @inheritdoc */
    public function tplContent() {
        html_login();
    }

}
