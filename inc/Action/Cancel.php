<?php

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionAbort;

/**
 * Class Cancel
 *
 * Alias for show. Aborts editing
 *
 * @package dokuwiki\Action
 */
class Cancel extends AbstractAliasAction {

    /** @inheritdoc */
    public function preProcess() {
        global $ID;
        unlock($ID);

        // continue with draftdel -> redirect -> show
        throw new ActionAbort('draftdel');
    }

}
