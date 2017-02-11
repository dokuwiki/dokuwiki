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
class Draftdel extends AbstractUserAction {

    /** @inheritdoc */
    function minimumPermission() {
        return AUTH_EDIT;
    }

    public function preProcess() {
        act_draftdel('fixme'); // FIXME replace this utility function
        throw new ActionAbort();
    }

}
