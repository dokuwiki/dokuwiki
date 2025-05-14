<?php

namespace dokuwiki\Extension;

/**
 * Action Plugin Prototype
 *
 * Handles action hooks within a plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 */
abstract class ActionPlugin extends Plugin
{
    /**
     * Registers a callback function for a given event
     *
     * @param EventHandler $controller
     */
    abstract public function register(EventHandler $controller);
}
