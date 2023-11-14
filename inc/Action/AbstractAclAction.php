<?php

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionAclRequiredException;
use dokuwiki\Extension\AuthPlugin;

/**
 * Class AbstractAclAction
 *
 * An action that requires the ACL subsystem to be enabled (eg. useacl=1)
 *
 * @package dokuwiki\Action
 */
abstract class AbstractAclAction extends AbstractAction
{
    /** @inheritdoc */
    public function checkPreconditions()
    {
        parent::checkPreconditions();
        global $conf;
        global $auth;
        if (!$conf['useacl']) throw new ActionAclRequiredException();
        if (!$auth instanceof AuthPlugin) throw new ActionAclRequiredException();
    }
}
