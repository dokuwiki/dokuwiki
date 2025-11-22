<?php

namespace easywiki\Action;

use easywiki\Action\Exception\ActionAbort;
use easywiki\Action\Exception\ActionException;
use easywiki\JWT;

class Authtoken extends AbstractUserAction
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

        if (!checkSecurityToken()) throw new ActionException('profile');
    }

    /** @inheritdoc */
    public function preProcess()
    {
        global $INPUT;
        parent::preProcess();
        $token = JWT::fromUser($INPUT->server->str('REMOTE_USER'));
        $token->save();
        throw new ActionAbort('profile');
    }
}
