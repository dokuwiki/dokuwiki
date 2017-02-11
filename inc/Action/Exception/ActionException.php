<?php

namespace dokuwiki\Action\Exception;

/**
 * Class ActionException
 *
 * This exception and its subclasses signal that the current action should be
 * aborted and a different action should be used instead. The new action can
 * be given as parameter in the constructor. Defaults to 'show'
 *
 * The message will NOT be shown to the enduser
 *
 * @package dokuwiki\Action\Exception
 */
class ActionException extends \Exception {

    protected $newaction;

    /**
     * ActionException constructor.
     *
     * @param string $newaction the action that should be used next
     * @param string $message optional message, will not be shown except for some dub classes
     */
    public function __construct($newaction = 'show', $message = '') {
        parent::__construct($message);
        $this->newaction = $newaction;
    }

    /**
     * Returns the action to use next
     *
     * @return string
     */
    public function getNewAction() {
        return $this->newaction;
    }
}
