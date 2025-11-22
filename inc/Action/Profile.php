<?php

namespace easywiki\Action;

use easywiki\Ui\UserProfile;
use easywiki\Action\Exception\ActionAbort;
use easywiki\Action\Exception\ActionDisabledException;
use easywiki\Extension\AuthPlugin;
use easywiki\Ui;

/**
 * Class Profile
 *
 * Handle the profile form
 *
 * @package easywiki\Action
 */
class Profile extends AbstractUserAction
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
        if (!$auth->canDo('Profile')) throw new ActionDisabledException();
    }

    /** @inheritdoc */
    public function preProcess()
    {
        global $lang;
        if (updateprofile()) {
            msg($lang['profchanged'], 1);
            throw new ActionAbort('show');
        }
    }

    /** @inheritdoc */
    public function tplContent()
    {
        (new UserProfile())->show();
    }
}
