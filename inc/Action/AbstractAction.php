<?php

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionDisabledException;
use dokuwiki\Action\Exception\ActionException;
use dokuwiki\Action\Exception\FatalException;

/**
 * Class AbstractAction
 *
 * Base class for all actions
 *
 * @package dokuwiki\Action
 */
abstract class AbstractAction {

    /** @var string holds the name of the action (lowercase class name, no namespace) */
    protected $actionname;

    /**
     * AbstractAction constructor.
     */
    public function __construct() {
        // http://stackoverflow.com/a/27457689/172068
        $this->actionname = strtolower(substr(strrchr(get_class($this), '\\'), 1));
    }

    /**
     * Return the minimum permission needed
     *
     * This needs to return one of the AUTH_* constants. It will be checked against
     * the current user and page after checkPermissions() ran through. If it fails,
     * the user will be shown the Denied action.
     *
     * @return int
     */
    abstract function minimumPermission();

    /**
     * Check permissions are correct to run this action
     *
     * @throws ActionException
     * @return void
     */
    public function checkPermissions() {
        if(!actionOK($this->actionname)) throw new ActionDisabledException();
    }

    /**
     * Process data
     *
     * This runs before any output is sent to the browser.
     *
     * Throw an Exception if a different action should be run after this step.
     *
     * @throws ActionException
     * @return void
     */
    public function preProcess() {
    }

    /**
     * Output whatever content is wanted within tpl_content();
     *
     * @fixme we may want to return a Ui class here
     */
    public function tplContent() {
        throw new FatalException('No content for Action ' . $this->actionname);
    }

    /**
     * @return string
     */
    public function getActionName() {
        return $this->actionname;
    }
}
