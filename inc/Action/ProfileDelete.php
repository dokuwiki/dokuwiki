<?php

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionAbort;
use dokuwiki\Action\Exception\ActionDisabledException;
use dokuwiki\Extension\AuthPlugin;

/**
 * Class ProfileDelete
 *
 * Delete a user account
 *
 * @package dokuwiki\Action
 */
class ProfileDelete extends AbstractUserAction
{
    /**
     * ProfileDelete constructor.
     *
     * The name of this action carries an underscore and thus cannot be derived
     * from the class name.
     *
     * @param string $actionname the name of this action
     */
    public function __construct($actionname = 'profile_delete')
    {
        parent::__construct($actionname);
    }

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
