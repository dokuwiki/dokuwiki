<?php
/**
 * Action Plugin Prototype
 *
 * All DokuWiki plugins to interfere with the event system
 * need to inherit from this class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 */
abstract class DokuWiki_Action_Plugin extends DokuWiki_Plugin {

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller
     */
    abstract public function register(Doku_Event_Handler $controller);
}
