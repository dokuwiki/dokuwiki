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

    /** @var string the new action */
    protected $newaction;

    /** @var bool should the exception's message be shown to the user? */
    protected $displayToUser = false;

    /**
     * ActionException constructor.
     *
     * When no new action is given 'show' is assumed. For requests that originated in a POST,
     * a 'redirect' is used which will cause a redirect to the 'show' action.
     *
     * @param string|null $newaction the action that should be used next
     * @param string $message optional message, will not be shown except for some dub classes
     */
    public function __construct($newaction = null, $message = '') {
        global $INPUT;
        parent::__construct($message);
        if(is_null($newaction)) {
            if(strtolower($INPUT->server->str('REQUEST_METHOD')) == 'post') {
                $newaction = 'redirect';
            } else {
                $newaction = 'show';
            }
        }

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

    /**
     * Should this Exception's message be shown to the user?
     *
     * @param null|bool $set when null is given, the current setting is not changed
     * @return bool
     */
    public function displayToUser($set = null) {
        if(!is_null($set)) $this->displayToUser = $set;
        return $set;
    }
}
