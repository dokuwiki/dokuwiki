<?php
/**
 * Created by IntelliJ IDEA.
 * User: andi
 * Date: 2/10/17
 * Time: 3:18 PM
 */

namespace dokuwiki;

use dokuwiki\Action\AbstractAction;
use dokuwiki\Action\Exception\ActionDisabledException;
use dokuwiki\Action\Exception\ActionException;
use dokuwiki\Action\Exception\FatalException;
use dokuwiki\Action\Exception\NoActionException;

class ActionRouter {

    /** @var  AbstractAction */
    protected $action;

    /** @var  ActionRouter */
    protected $instance;

    /**
     * ActionRouter constructor. Singleton, thus protected!
     */
    protected function __construct() {
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
     * seta $action
     *
     * @param string $actionname
     * @fixme implement redirect on action change with post
     * @fixme add event handling
     * @fixme add the action name back to $ACT for plugins relying on it
     */
    protected function setupAction($actionname) {
        try {
            $this->action = $this->loadAction($actionname);
            $this->action->checkPermissions();
            $this->ensureMinimumPermission($this->action->minimumPermission());
            $this->action->preProcess();

        } catch(ActionException $e) {
            // we should have gotten a new action
            $newaction = $e->getNewAction();

            // no infinite recursion
            if($newaction === $actionname) {
                // FIXME this doesn't catch larger circles
                $this->handleFatalException(new FatalException('Infinite loop in actions', 500, $e));
            }

            // this one should trigger a user message
            if(is_a($e, ActionDisabledException::class)) {
                msg('Action disabled: ' . hsc($actionname), -1);
            }

            // do setup for new action
            $this->setupAction($newaction);

        } catch(NoActionException $e) {
            // FIXME here the unknown event needs to be run
            $this->action = $this->loadAction('show');

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
