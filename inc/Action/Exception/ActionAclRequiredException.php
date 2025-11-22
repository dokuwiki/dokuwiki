<?php

namespace easywiki\Action\Exception;

/**
 * Class ActionAclRequiredException
 *
 * Thrown by AbstractACLAction when an action requires that the ACL subsystem is
 * enabled but it isn't. You should not use it
 *
 * The message will NOT be shown to the enduser
 *
 * @package easywiki\Action\Exception
 */
class ActionAclRequiredException extends ActionException
{
}
