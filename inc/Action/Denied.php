<?php

namespace dokuwiki\Action;

/**
 * Class Denied
 *
 * Show the access denied screen
 *
 * @package dokuwiki\Action
 */
class Denied extends AbstractAclAction {

    /** @inheritdoc */
    function minimumPermission() {
        return AUTH_NONE;
    }

    public function tplContent() {
        html_denied();
    }

}
