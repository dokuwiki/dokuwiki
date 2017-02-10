<?php

namespace dokuwiki\Action\Exception;

/**
 * Class FatalException
 *
 * A fatal exception during handling the action
 *
 * Will abort all handling displaying some info to the user
 *
 * @package dokuwiki\Action\Exception
 */
class FatalException extends \Exception {

    protected $status;

    /**
     * FatalException constructor.
     *
     * @param string $message the message to send
     * @param int $status the HTTP status to send
     * @param null|\Exception $previous previous exception
     */
    public function __construct($message = 'A fatal error occured', $status = 500, $previous = null) {
        parent::__construct($message, $status, $previous);
    }
}
