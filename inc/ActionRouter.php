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
    protected $instance;

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
    public function getInstance($reinit = false) {
        if(($this->instance === null) || $reinit) {
            $this->instance = new ActionRouter();
        }
        return $this->instance;
    }

    /**
     * Setup the given action
     *
     * Instantiates the right class, runs permission checks and pre-processing and
     * sets $action
     *
     * @param string $actionname
     * @triggers ACTION_ACT_PREPROCESS
     * @fixme implement redirect on action change with post
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

            // no infinite recursion
            if($actionname == $presetup) {
                // FIXME this doesn't catch larger circles
                $this->handleFatalException(new FatalException('Infinite loop in actions', 500, $e));
            }

            // this one should trigger a user message
            if(is_a($e, ActionDisabledException::class)) {
                msg('Action disabled: ' . hsc($presetup), -1);
            }

            // do setup for new action
            $this->setupAction($actionname);

        } catch(NoActionException $e) {
            // give plugins an opportunity to process the actionname
            $evt = new \Doku_Event('ACTION_ACT_PREPROCESS', $actionname);
            if($evt->advise_before()) {
                if($actionname == $presetup) {
                    // no plugin changed the action, complain and switch to show
                    msg('Action unknown: ' . hsc($actionname), -1);
                    $actionname = 'show';
                }
                $this->setupAction($actionname);
            } else {
                // event said the action should be kept, assume action plugin will handle it later
                $this->action = new Plugin();
                $this->action->setActionName($actionname);
            }
            $evt->advise_after();

        } catch(\Exception $e) {
            $this->handleFatalException($e);
        }
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
     * @param $actionname
     * @return AbstractAction
     * @throws NoActionException
     */
    protected function loadAction($actionname) {
        $class = 'dokuwiki\\Action\\' . ucfirst(strtolower($actionname));
        if(class_exists($class)) {
            return new $class;
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
