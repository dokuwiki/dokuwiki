<?php

namespace dokuwiki\Extension;

/**
 * The Action plugin event
 */
class Event
{

    // public properties
    public $name = '';                // READONLY  event name, objects must register against this name to see the event
    public $data = null;              // READWRITE data relevant to the event, no standardised format (YET!)
    public $result = null;            // READWRITE the results of the event action, only relevant in "_AFTER" advise
    //    event handlers may modify this if they are preventing the default action
    //    to provide the after event handlers with event results
    public $canPreventDefault = true; // READONLY  if true, event handlers can prevent the events default action

    // private properties, event handlers can effect these through the provided methods
    protected $_default = true;     // whether or not to carry out the default action associated with the event
    protected $_continue = true;    // whether or not to continue propagating the event to other handlers

    /**
     * event constructor
     *
     * @param string $name
     * @param mixed $data
     */
    public function __construct($name, &$data)
    {

        $this->name = $name;
        $this->data =& $data;

    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * advise functions
     *
     * advise all registered handlers of this event
     *
     * if these methods are used by functions outside of this object, they must
     * properly handle correct processing of any default action and issue an
     * advise_after() signal. e.g.
     *    $evt = new dokuwiki\Plugin\Doku_Event(name, data);
     *    if ($evt->advise_before(canPreventDefault) {
     *      // default action code block
     *    }
     *    $evt->advise_after();
     *    unset($evt);
     *
     * @param bool $enablePreventDefault
     * @return bool results of processing the event, usually $this->_default
     */
    public function advise_before($enablePreventDefault = true)
    {
        global $EVENT_HANDLER;

        $this->canPreventDefault = $enablePreventDefault;
        $EVENT_HANDLER->process_event($this, 'BEFORE');

        return (!$enablePreventDefault || $this->_default);
    }

    public function advise_after()
    {
        global $EVENT_HANDLER;

        $this->_continue = true;
        $EVENT_HANDLER->process_event($this, 'AFTER');
    }

    /**
     * trigger
     *
     * - advise all registered (<event>_BEFORE) handlers that this event is about to take place
     * - carry out the default action using $this->data based on $enablePrevent and
     *   $this->_default, all of which may have been modified by the event handlers.
     * - advise all registered (<event>_AFTER) handlers that the event has taken place
     *
     * @param null|callable $action
     * @param bool $enablePrevent
     * @return  mixed $event->results
     *          the value set by any <event>_before or <event> handlers if the default action is prevented
     *          or the results of the default action (as modified by <event>_after handlers)
     *          or NULL no action took place and no handler modified the value
     */
    public function trigger($action = null, $enablePrevent = true)
    {

        if (!is_callable($action)) {
            $enablePrevent = false;
            if (!is_null($action)) {
                trigger_error(
                    'The default action of ' . $this .
                    ' is not null but also not callable. Maybe the method is not public?',
                    E_USER_WARNING
                );
            }
        }

        if ($this->advise_before($enablePrevent) && is_callable($action)) {
            if (is_array($action)) {
                list($obj, $method) = $action;
                $this->result = $obj->$method($this->data);
            } else {
                $this->result = $action($this->data);
            }
        }

        $this->advise_after();

        return $this->result;
    }

    /**
     * stopPropagation
     *
     * stop any further processing of the event by event handlers
     * this function does not prevent the default action taking place
     */
    public function stopPropagation()
    {
        $this->_continue = false;
    }

    /**
     * may the event propagate to the next handler?
     *
     * @return bool
     */
    public function mayPropagate()
    {
        return $this->_continue;
    }

    /**
     * preventDefault
     *
     * prevent the default action taking place
     */
    public function preventDefault()
    {
        $this->_default = false;
    }

    /**
     * should the default action be executed?
     *
     * @return bool
     */
    public function mayRunDefault()
    {
        return $this->_default;
    }

    /**
     * Convenience method to trigger an event
     *
     * Creates, triggers and destroys an event in one go
     *
     * @param  string   $name               name for the event
     * @param  mixed    $data               event data
     * @param  callable $action             (optional, default=NULL) default action, a php callback function
     * @param  bool     $canPreventDefault  (optional, default=true) can hooks prevent the default action
     *
     * @return mixed                        the event results value after all event processing is complete
     *                                      by default this is the return value of the default action however
     *                                      it can be set or modified by event handler hooks
     */
    static public function createAndTrigger($name, &$data, $action=null, $canPreventDefault=true) {
        $evt = new Event($name, $data);
        return $evt->trigger($action, $canPreventDefault);
    }
}
