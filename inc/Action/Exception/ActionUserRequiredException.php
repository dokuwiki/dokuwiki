<?php

namespace dokuwiki\Action\Exception;

/**
 * Class ActionUserRequiredException
 *
 * Thrown by AbstractUserAction when an action requires that a user is logged
 * in but it isn't. You should not use it.
 *
 * The message will NOT be shown to the enduser
 *
 * @package dokuwiki\Action\Exception
 */
class ActionUserRequiredException extends ActionException {

}
