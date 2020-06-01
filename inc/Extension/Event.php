<?php
// phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

namespace dokuwiki\Extension;

/**
 * The Action plugin event
 */
class Event
{
    /** @var string READONLY  event name, objects must register against this name to see the event */
    public $name = '';
    /** @var mixed|null READWRITE data relevant to the event, no standardised format, refer to event docs */
    public $data = null;
    /**
     * @var mixed|null READWRITE the results of the event action, only relevant in "_AFTER" advise
     *                 event handlers may modify this if they are preventing the default action
     *                 to provide the after event handlers with event results
     */
    public $result = null;
    /** @var bool READONLY  if true, event handlers can prevent the events default action */
    public $canPreventDefault = true;

    /** @var bool whether or not to carry out the default action associated with the event */
    protected $runDefault = true;
    /** @var bool whether or not to continue propagating the event to other handlers */
    protected $mayContinue = true;

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
     * advise all registered BEFORE handlers of this event
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
     * @return bool results of processing the event, usually $this->runDefault
     */
    public function advise_before($enablePreventDefault = true)
    {
        global $EVENT_HANDLER;

        $this->canPreventDefault = $enablePreventDefault;
        if ($EVENT_HANDLER !== null) {
            $EVENT_HANDLER->process_event($this, 'BEFORE');
        } else {
            dbglog($this->name . ':BEFORE event triggered before event system was initialized');
        }

        return (!$enablePreventDefault || $this->runDefault);
    }

    /**
     * advise all registered AFTER handlers of this event
     *
     * @param bool $enablePreventDefault
     * @see advise_before() for details
     */
    public function advise_after()
    {
        global $EVENT_HANDLER;

        $this->mayContinue = true;

        if ($EVENT_HANDLER !== null) {
            $EVENT_HANDLER->process_event($this, 'AFTER');
        } else {
            dbglog($this->name . ':AFTER event triggered before event system was initialized');
        }
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
            if ($action !== null) {
                trigger_error(
                    'The default action of ' . $this .
                    ' is not null but also not callable. Maybe the method is not public?',
                    E_USER_WARNING
                );
            }
        }

        if ($this->advise_before($enablePrevent) && is_callable($action)) {
            $this->result = call_user_func_array($action, [&$this->data]);
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
        $this->mayContinue = false;
    }

    /**
     * may the event propagate to the next handler?
     *
     * @return bool
     */
    public function mayPropagate()
    {
        return $this->mayContinue;
    }

    /**
     * preventDefault
     *
     * prevent the default action taking place
     */
    public function preventDefault()
    {
        $this->runDefault = false;
    }

    /**
     * should the default action be executed?
     *
     * @return bool
     */
    public function mayRunDefault()
    {
        return $this->runDefault;
    }

    /**
     * Convenience method to trigger an event
     *
     * Creates, triggers and destroys an event in one go
     *
     * @param string $name name for the event
     * @param mixed $data event data
     * @param callable $action (optional, default=NULL) default action, a php callback function
     * @param bool $canPreventDefault (optional, default=true) can hooks prevent the default action
     *
     * @return mixed                        the event results value after all event processing is complete
     *                                      by default this is the return value of the default action however
     *                                      it can be set or modified by event handler hooks
     */
    static public function createAndTrigger($name, &$data, $action = null, $canPreventDefault = true)
    {
        $evt = new Event($name, $data);
        return $evt->trigger($action, $canPreventDefault);
    }
}
