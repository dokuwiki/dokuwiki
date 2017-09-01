<?php

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionAbort;

/**
 * Class Draftdel
 *
 * Delete a draft
 *
 * @package dokuwiki\Action
 */
class Draftdel extends AbstractAction {

    /** @inheritdoc */
    public function minimumPermission() {
        return AUTH_EDIT;
    }

    /**
     * Delete an existing draft if any
     *
     * Reads draft information from $INFO. Redirects to show, afterwards.
     *
     * @throws ActionAbort
     */
    public function preProcess() {
        global $INFO;
        @unlink($INFO['draft']);
        $INFO['draft'] = null;

        throw new ActionAbort('redirect');
    }

}
