<?php

namespace easywiki\Action;

use easywiki\Action\Exception\ActionAbort;
use easywiki\Action\Exception\ActionDisabledException;
use easywiki\Extension\AuthPlugin;

/**
 * Class ProfileDelete
 *
 * Delete a user account
 *
 * @package easywiki\Action
 */
class ProfileDelete extends AbstractUserAction
{
    /** @inheritdoc */
    public function minimumPermission()
    {
        return AUTH_NONE;
    }

    /** @inheritdoc */
    public function checkPreconditions()
    {
        parent::checkPreconditions();

        /** @var AuthPlugin $auth */
        global $auth;
        if (!$auth->canDo('delUser')) throw new ActionDisabledException();
    }

    /** @inheritdoc */
    public function preProcess()
    {
        global $lang;
        if (auth_deleteprofile()) {
            msg($lang['profdeleted'], 1);
            throw new ActionAbort('show');
        } else {
            throw new ActionAbort('profile');
        }
    }
}
