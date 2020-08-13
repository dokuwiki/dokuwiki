<?php

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionException;

/**
 * Class Login
 *
 * The login form. Actual logins are handled in inc/auth.php
 *
 * @package dokuwiki\Action
 */
class Login extends AbstractAclAction {

    /** @inheritdoc */
    public function minimumPermission() {
        return AUTH_NONE;
    }

    /** @inheritdoc */
    public function checkPreconditions() {
        global $INPUT;
        parent::checkPreconditions();
        if($INPUT->server->has('REMOTE_USER')) {
            // nothing to do
            throw new ActionException();
        }
    }

    /** @inheritdoc */
    public function tplContent() {
        html_login();
    }

}
