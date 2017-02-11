<?php

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionUserRequiredException;

/**
 * Class AbstractUserAction
 *
 * An action that requires a logged in user
 *
 * @package dokuwiki\Action
 */
abstract class AbstractUserAction extends AbstractAclAction {

    /** @inheritdoc */
    public function checkPermissions() {
        parent::checkPermissions();
        global $INPUT;
        if(!$INPUT->server->str('REMOTE_USER')) {
            throw new ActionUserRequiredException();
        }
    }

}
