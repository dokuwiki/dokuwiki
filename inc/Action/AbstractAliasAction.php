<?php

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionAclRequiredException;
use dokuwiki\Action\Exception\ActionException;

abstract class AbstractAliasAction extends AbstractAction {

    /** @inheritdoc */
    function minimumPermission() {
        return AUTH_NONE;
    }

}
