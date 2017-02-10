<?php

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionAclRequiredException;
use dokuwiki\Action\Exception\ActionException;

abstract class AbstractAclAction extends AbstractAction {

    /** @inheritdoc */
    public function checkPermissions() {
        parent::checkPermissions();
        global $conf;
        if(!$conf['useacl']) throw new ActionAclRequiredException();
    }

}
