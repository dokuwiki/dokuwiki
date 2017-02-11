<?php
/**
 * Created by IntelliJ IDEA.
 * User: andi
 * Date: 2/11/17
 * Time: 9:18 AM
 */

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionAbort;

class Register extends AbstractAction {

    /** @inheritdoc */
    function minimumPermission() {
        return AUTH_NONE;
    }

    public function preProcess() {
        if(register()) { // FIXME could be moved from auth to here
            throw new ActionAbort('login');
        }
    }

    public function tplContent() {
        html_register();
    }

}
