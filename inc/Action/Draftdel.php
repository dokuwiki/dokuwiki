<?php

namespace easywiki\Action;

use easywiki\Draft;
use easywiki\Action\Exception\ActionAbort;

/**
 * Class Draftdel
 *
 * Delete a draft
 *
 * @package easywiki\Action
 */
class Draftdel extends AbstractAction
{
    /** @inheritdoc */
    public function minimumPermission()
    {
        return AUTH_EDIT;
    }

    /**
     * Delete an existing draft for the current page and user if any
     *
     * Redirects to show, afterwards.
     *
     * @throws ActionAbort
     */
    public function preProcess()
    {
        global $INFO, $ID;
        $draft = new Draft($ID, $INFO['client']);
        if ($draft->isDraftAvailable() && checkSecurityToken()) {
            $draft->deleteDraft();
        }

        throw new ActionAbort('redirect');
    }
}
