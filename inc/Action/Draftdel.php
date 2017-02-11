<?php
/**
 * Created by IntelliJ IDEA.
 * User: andi
 * Date: 2/11/17
 * Time: 10:13 AM
 */

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionAbort;

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
