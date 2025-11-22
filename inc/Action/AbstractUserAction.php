<?php

namespace easywiki\Action;

use easywiki\Action\Exception\ActionUserRequiredException;

/**
 * Class AbstractUserAction
 *
 * An action that requires a logged in user
 *
 * @package easywiki\Action
 */
abstract class AbstractUserAction extends AbstractAclAction
{
    /** @inheritdoc */
    public function checkPreconditions()
    {
        parent::checkPreconditions();
        global $INPUT;
        if ($INPUT->server->str('REMOTE_USER') === '') {
            throw new ActionUserRequiredException();
        }
    }
}
