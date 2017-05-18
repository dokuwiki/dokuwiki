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

    public function preProcess() {
        throw new ActionAbort('draftdel');
    }

}
