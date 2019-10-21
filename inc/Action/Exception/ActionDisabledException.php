<?php

namespace dokuwiki\Action\Exception;

/**
 * Class ActionDisabledException
 *
 * Thrown when the requested action has been disabled. Eg. through the 'disableactions'
 * config setting. You should probably not use it.
 *
 * The message will NOT be shown to the enduser, but a generic information will be shown.
 *
 * @package dokuwiki\Action\Exception
 */
class ActionDisabledException extends ActionException {

}
