<?php

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionAbort;
use dokuwiki\Action\Exception\ActionDisabledException;
use dokuwiki\Extension\AuthPlugin;
use dokuwiki\Ui;

/**
 * Class Profile
 *
 * Handle the profile form
 *
 * @package dokuwiki\Action
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
        if(!$auth->canDo('Profile')) throw new ActionDisabledException();
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
        (new Ui\UserProfile)->show();
    }

}
