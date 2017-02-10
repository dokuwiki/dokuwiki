<?php
/**
 * Created by IntelliJ IDEA.
 * User: andi
 * Date: 2/10/17
 * Time: 3:16 PM
 */

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionAbort;

class Check extends AbstractAction {

    /** @inheritdoc */
    function minimumPermission() {
        return AUTH_READ;
    }

    public function preProcess() {
        check();
        throw new ActionAbort();
    }

}
