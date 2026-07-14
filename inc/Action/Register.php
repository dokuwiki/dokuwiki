<?php

namespace dokuwiki\Action;

use dokuwiki\Ui\UserRegister;
use dokuwiki\Action\Exception\ActionAbort;
use dokuwiki\Action\Exception\ActionDisabledException;
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

        // actionOK() bundles the disableactions, legacy openregister and addUser capability checks
        if (!actionOK('register')) throw new ActionDisabledException();
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
