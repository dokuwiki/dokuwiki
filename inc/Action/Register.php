<?php

namespace dokuwiki\Action;

use dokuwiki\Ui\UserRegister;
use dokuwiki\Action\Exception\ActionAbort;
use dokuwiki\Action\Exception\ActionDisabledException;
use dokuwiki\Extension\AuthPlugin;
use dokuwiki\Ui;

/**
 * Class Register
 *
 * Self registering a new user
 *
 * @package dokuwiki\Action
 */
class Register extends AbstractAclAction
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
        global $conf;
        if (isset($conf['openregister']) && !$conf['openregister']) throw new ActionDisabledException();
        if (!$auth->canDo('addUser')) throw new ActionDisabledException();
    }

    /** @inheritdoc */
    public function preProcess()
    {
        if (register()) { // FIXME could be moved from auth to here
            throw new ActionAbort('login');
        }
    }

    /** @inheritdoc */
    public function tplContent()
    {
        (new UserRegister())->show();
    }
}
