<?php

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionAbort;

/**
 * Class Check
 *
 * Adds some debugging info before aborting to show
 *
 * @package dokuwiki\Action
 */
class Check extends AbstractAction {

    /** @inheritdoc */
    public function minimumPermission() {
        return AUTH_READ;
    }

    public function preProcess() {
        check();
        throw new ActionAbort();
    }

}
