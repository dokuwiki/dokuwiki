<?php
/**
 * DokuWiki Events
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');
require_once(DOKU_INC.'inc/pluginutils.php');

class Doku_Event {

  // public properties
  var $name = '';                // READONLY  event name, objects must register against this name to see the event
  var $data = NULL;              // READWRITE data relevant to the event, no standardised format (YET!)
  var $result = NULL;            // READWRITE the results of the event action, only relevant in "_AFTER" advise
                                 //    event handlers may modify this if they are preventing the default action
                                 //    to provide the after event handlers with event results
  var $canPreventDefault = true; // READONLY  if true, event handlers can prevent the events default action

  // private properties, event handlers can effect these through the provided methods
  var $_default = true;     // whether or not to carry out the default action associated with the event
  var $_continue = true;    // whether or not to continue propagating the event to other handlers

  /**
   * event constructor
   */
  function Doku_Event($name, &$data) {

    $this->name = $name;
    $this->data =& $data;

  }

  /**
   * advise functions
   *
   * advise all registered handlers of this event
   *
   * if these methods are used by functions outside of this object, they must
   * properly handle correct processing of any default action and issue an
   * advise_after() signal. e.g.
   *    $evt = new Doku_Event(name, data);
   *    if ($evt->advise_before(canPreventDefault) {
   *      // default action code block
   *    }
   *    $evt->advise_after();
   *    unset($evt);
   *
   * @return  results of processing the event, usually $this->_default
   */
  function advise_before($enablePreventDefault=true) {
    global $EVENT_HANDLER;

    return $EVENT_HANDLER->process_event($this,'BEFORE');
  }

  function advise_after() {
    global $EVENT_HANDLER;

    $this->_continue = true;    
    $EVENT_HANDLER->process_event($this,'AFTER');
  }

  /**
   * trigger
   *
   * - advise all registered (<event>_BEFORE) handlers that this event is about to take place
   * - carry out the default action using $this->data based on $enablePrevent and
   *   $this->_default, all of which may have been modified by the event handlers. 
   * - advise all registered (<event>_AFTER) handlers that the event has taken place
   *
   * @return  $event->results
   *          the value set by any <event>_before or <event> handlers if the default action is prevented
   *          or the results of the default action (as modified by <event>_after handlers)
   *          or NULL no action took place and no handler modified the value
   */
  function trigger($action=NULL, $enablePrevent=true) {

    if (!is_callable($action)) $enablePrevent = false;
    $this->canPreventDefault = $enablePrevent;
    
    $this->advise_before($enablePrevent);

    if (is_callable($action)) {
      if (!$enablePrevent || $this->_default) {
        if (is_array($action)) {
          list($obj,$method) = $action;
          $this->result = $obj->$method($this->data);
        } else {
          $this->result = $action($this->data);
        }
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
  function stopPropagation() { $this->_continue = false;  }  

  /**
   * preventDefault
   *
   * prevent the default action taking place
   */
  function preventDefault() { $this->_default = false;  }
}

class Doku_Event_Handler {

  // public properties:  none

  // private properties
  var $_hooks = array();          // array of events and their registered handlers

  /*
   * event_handler
   *
   * constructor, loads all action plugins and calls their register() method giving them
   * an opportunity to register any hooks they require
   */
  function Doku_Event_Handler() {

    // load action plugins
    $plugin = NULL;
    $pluginlist = plugin_list('action');

    foreach ($pluginlist as $plugin_name) {
      $plugin =& plugin_load('action',$plugin_name);

      if ($plugin !== NULL) $plugin->register($this);
    }
  }

  /*
   * register_hook
   *
   * register a hook for an event
   *
   * @PARAM  $event   (string)   name used by the event, (incl '_before' or '_after' for triggers)
   * @PARAM  $obj     (obj)      object in whose scope method is to be executed, 
   *                             if NULL, method is assumed to be a globally available function
   * @PARAM  $method  (function) event handler function
   * @PARAM  $param   (mixed)    data passed to the event handler
   */ 
  function register_hook($event, $advise, &$obj, $method, $param=NULL) {
    $this->_hooks[$event.'_'.$advise][] = array($obj, $method, $param);
  }

  function process_event(&$event,$advise='') {

    $evt_name = $event->name . ($advise ? '_'.$advise : '_BEFORE');

    if (!empty($this->_hooks[$evt_name])) {
      $hook = reset($this->_hooks[$evt_name]);
      do {
        list($obj, $method, $param) = $hook;
        if (is_null($obj)) {
          $method($event, $param);
        } else {
          $obj->$method($event, $param);
        }

      } while ($event->_continue && $hook = next($this->_hooks[$evt_name]));
    }

    return $event->_default;
  }
}

// function wrapper to enable one line event triggering
function trigger_event($name, &$data, $action=NULL, $canPreventDefault=true) {
  
  $evt = new Doku_Event($name, $data);
  return $evt->trigger($action, $canPreventDefault);
}

// create the event handler
$EVENT_HANDLER = new Doku_Event_Handler();
