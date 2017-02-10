<?php

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionAclRequiredException;
use dokuwiki\Action\Exception\ActionException;
use dokuwiki\Action\Exception\ActionNoUserException;

abstract class AbstractUserAction extends AbstractAclAction {

    /** @inheritdoc */
    public function checkPermissions() {
        parent::checkPermissions();
        global $INPUT;
        if(!$INPUT->server->str('REMOTE_USER')) {
            throw new ActionNoUserException();
        }
    }

}
