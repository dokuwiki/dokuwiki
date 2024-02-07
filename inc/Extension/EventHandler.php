<?php

// phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

namespace dokuwiki\Extension;

/**
 * Controls the registration and execution of all events,
 */
class EventHandler
{
    // public properties:  none

    // private properties
    protected $hooks = [];          // array of events and their registered handlers

    /**
     * event_handler
     *
     * constructor, loads all action plugins and calls their register() method giving them
     * an opportunity to register any hooks they require
     */
    public function __construct()
    {

        // load action plugins
        /** @var ActionPlugin $plugin */
        $plugin = null;
        $pluginlist = plugin_list('action');

        foreach ($pluginlist as $plugin_name) {
            $plugin = plugin_load('action', $plugin_name);

            if ($plugin instanceof PluginInterface) $plugin->register($this);
        }
    }

    /**
     * register_hook
     *
     * register a hook for an event
     *
     * @param string $event name used by the event
     * @param string $advise BEFORE|AFTER
     * @param object $obj scope for the method be executed on, NULL for global function or callable
     * @param string|callable $method event handler function
     * @param mixed $param data passed to the event handler
     * @param int $seq sequence number for ordering hook execution (ascending)
     */
    public function register_hook($event, $advise, $obj, $method, $param = null, $seq = 0)
    {
        $seq = (int)$seq;
        $doSort = !isset($this->hooks[$event . '_' . $advise][$seq]);
        $this->hooks[$event . '_' . $advise][$seq][] = [$obj, $method, $param];

        if ($doSort) {
            ksort($this->hooks[$event . '_' . $advise]);
        }
    }

    /**
     * process the before/after event
     *
     * @param Event $event
     * @param string $advise BEFORE or AFTER
     */
    public function process_event($event, $advise = '')
    {

        $evt_name = $event->name . ($advise ? '_' . $advise : '_BEFORE');

        if (!empty($this->hooks[$evt_name])) {
            foreach ($this->hooks[$evt_name] as $sequenced_hooks) {
                foreach ($sequenced_hooks as $hook) {
                    [$obj, $method, $param] = $hook;

                    if ($obj === null) {
                        $method($event, $param);
                    } else {
                        $obj->$method($event, $param);
                    }

                    if (!$event->mayPropagate()) return;
                }
            }
        }
    }

    /**
     * Check if an event has any registered handlers
     *
     * When $advise is empty, both BEFORE and AFTER events will be considered,
     * otherwise only the given advisory is checked
     *
     * @param string $name Name of the event
     * @param string $advise BEFORE, AFTER or empty
     * @return bool
     */
    public function hasHandlerForEvent($name, $advise = '')
    {
        if ($advise) {
            return isset($this->hooks[$name . '_' . $advise]);
        }

        return isset($this->hooks[$name . '_BEFORE']) || isset($this->hooks[$name . '_AFTER']);
    }

    /**
     * Get all hooks and their currently registered handlers
     *
     * The handlers are sorted by sequence, then by register time
     *
     * @return array
     */
    public function getEventHandlers()
    {
        return $this->hooks;
    }
}
