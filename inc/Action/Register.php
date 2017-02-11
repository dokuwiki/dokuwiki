<?php

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionAbort;

/**
 * Class Register
 *
 * Self registering a new user
 *
 * @package dokuwiki\Action
 */
class Register extends AbstractAction {

    /** @inheritdoc */
    function minimumPermission() {
        return AUTH_NONE;
    }

    /** @inheritdoc */
    public function preProcess() {
        if(register()) { // FIXME could be moved from auth to here
            throw new ActionAbort('login');
        }
    }

    /** @inheritdoc */
    public function tplContent() {
        html_register();
    }

}
