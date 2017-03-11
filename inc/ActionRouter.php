<?php

namespace dokuwiki;

use dokuwiki\Action\AbstractAction;
use dokuwiki\Action\Exception\ActionDisabledException;
use dokuwiki\Action\Exception\ActionException;
use dokuwiki\Action\Exception\FatalException;
use dokuwiki\Action\Exception\NoActionException;
use dokuwiki\Action\Plugin;

/**
 * Class ActionRouter
 * @package dokuwiki
 */
class ActionRouter {

    /** @var  AbstractAction */
    protected $action;

    /** @var  ActionRouter */
    protected static $instance;

    /** @var int transition counter */
    protected $transitions = 0;

    /** maximum loop */
    const MAX_TRANSITIONS = 5;

    /**
     * ActionRouter constructor. Singleton, thus protected!
     *
     * Sets up the correct action based on the $ACT global. Writes back
     * the selected action to $ACT
     */
    protected function __construct() {
        global $ACT;
        $ACT = act_clean($ACT);
        $this->setupAction($ACT);
        $ACT = $this->action->getActionName();
    }

    /**
     * Get the singleton instance
     *
     * @param bool $reinit
     * @return ActionRouter
     */
    public static function getInstance($reinit = false) {
        if((self::$instance === null) || $reinit) {
            self::$instance = new ActionRouter();
        }
        return self::$instance;
    }

    /**
     * Setup the given action
     *
     * Instantiates the right class, runs permission checks and pre-processing and
     * sets $action
     *
     * @param string $actionname
     * @triggers ACTION_ACT_PREPROCESS
     */
    protected function setupAction($actionname) {
        $presetup = $actionname;

        try {
            $this->action = $this->loadAction($actionname);
            $this->action->checkPermissions();
            $this->ensureMinimumPermission($this->action->minimumPermission());
            $this->action->preProcess();

        } catch(ActionException $e) {
            // we should have gotten a new action
            $actionname = $e->getNewAction();

            // this one should trigger a user message
            if(is_a($e, ActionDisabledException::class)) {
                msg('Action disabled: ' . hsc($presetup), -1);
            }

            // do setup for new action
            $this->transitionAction($presetup, $actionname);

        } catch(NoActionException $e) {
            // give plugins an opportunity to process the actionname
            $evt = new \Doku_Event('ACTION_ACT_PREPROCESS', $actionname);
            if($evt->advise_before()) {
                if($actionname == $presetup) {
                    // no plugin changed the action, complain and switch to show
                    msg('Action unknown: ' . hsc($actionname), -1);
                    $actionname = 'show';
                }
                $this->transitionAction($presetup, $actionname);
            } else {
                // event said the action should be kept, assume action plugin will handle it later
                $this->action = new Plugin($actionname);
            }
            $evt->advise_after();

        } catch(\Exception $e) {
            $this->handleFatalException($e);
        }
    }

    /**
     * Transitions from one action to another
     *
     * Basically just calls setupAction() again but does some checks before. Also triggers
     * redirects for POST to show transitions
     *
     * @param string $from current action name
     * @param string $to new action name
     * @param null|ActionException $e any previous exception that caused the transition
     */
    protected function transitionAction($from, $to, $e = null) {
        global $INPUT;
        global $ID;

        $this->transitions++;

        // no infinite recursion
        if($from == $to) {
            $this->handleFatalException(new FatalException('Infinite loop in actions', 500, $e));
        }

        // larger loops will be caught here
        if($this->transitions >= self::MAX_TRANSITIONS) {
            $this->handleFatalException(new FatalException('Maximum action transitions reached', 500, $e));
        }

        // POST transitions to show should be a redirect
        if($to == 'show' && $from != $to && strtolower($INPUT->server->str('REQUEST_METHOD')) == 'post') {
            act_redirect($ID, $from); // FIXME we may want to move this function to the class
        }

        // do the recursion
        $this->setupAction($to);
    }

    /**
     * Check that the given minimum permissions are reached
     *
     * @param int $permneed
     * @throws ActionException
     */
    protected function ensureMinimumPermission($permneed) {
        global $INFO;
        if($INFO['perm'] < $permneed) {
            throw new ActionException('denied');
        }
    }

    /**
     * Aborts all processing with a message
     *
     * When a FataException instanc is passed, the code is treated as Status code
     *
     * @param \Exception|FatalException $e
     */
    protected function handleFatalException(\Exception $e) {
        if(is_a($e, FatalException::class)) {
            http_status($e->getCode());
        } else {
            http_status(500);
        }
        $msg = 'Something unforseen has happened: ' . $e->getMessage();
        nice_die(hsc($msg));
    }

    /**
     * Load the given action
     *
     * This translates the given name to a class name by uppercasing the first letter.
     * Underscores translate to camelcase names. For actions with underscores, the different
     * parts are removed beginning from the end until a matching class is found. The instatiated
     * Action will always have the full original action set as Name
     *
     * Example: 'export_raw' -> ExportRaw then 'export' -> 'Export'
     *
     * @param $actionname
     * @return AbstractAction
     * @throws NoActionException
     */
    protected function loadAction($actionname) {
        $actionname = strtolower($actionname); // FIXME is this needed here? should we run a cleanup somewhere else?
        $parts = explode('_', $actionname);
        while($parts) {
            $load = join('_', $parts);
            $class = 'dokuwiki\\Action\\' . str_replace('_', '', ucwords($load, '_'));
            if(class_exists($class)) {
                return new $class($actionname);
            }
            array_pop($parts);
        }

        throw new NoActionException();
    }

    /**
     * Returns the action handling the current request
     *
     * @return AbstractAction
     */
    public function getAction() {
        return $this->action;
    }
}
