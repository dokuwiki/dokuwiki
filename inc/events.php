<?php
/**
 * DokuWiki Events
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');
require_once(DOKU_INC.'inc/pluginutils.php');
class event {

  // public properties
  var $name = '';           // event name, objects must register against this name to see the event
  var $data = NULL;         // data relevant to the event, no standardised format (YET!)
  var $result = NULL;       // the results of the event action, only relevant in "*_after" advise

  // private properties
  var $_default = true;     // whether or not to carry out the default action associated with the event
  var $_continue = true;    // whether or not to continue propagating the event to other handlers
  var $_action = NULL;      // the function executed to carry out the event

  /**
   * event constructor
   */
  function event($name, &$data, $fn=NULL) {

    $this->name = $name;
    $this->data =& $data;
    $this->_action = $fn;

  }

  /**
   * advise
   *
   * advise all registered handlers of this event
   * any processing based on the event's _default property to be determined by the caller
   *
   * @return  results of processing the event, usually $this->_default
   */
  function advise() {
    global $EVENT_HANDLER;

    return $EVENT_HANDLER->process_event($this,'');
  }

  /**
   * trigger
   *
   * advise all registered (<event>_before) handlers that this event is about to take place
   * carry out the default action using $this->data based on $this->_default, all of which
   * may have been modified by the event handlers
   * if the action was carried out, advise all registered (<event>_after) handlers that the
   * event has taken place
   *
   * @return  $event->results
   *          the value set by any <event>_before or <event> handlers if the default action is prevented
   *          or the results of the default action (as modified by <event>_after handlers)
   *          or NULL no action took place and no handler modified the value
   */
  function trigger() {
    global $EVENT_HANDLER;

    $EVENT_HANDLER->process_event($this,'before');
    if ($this->_continue) $EVENT_HANDLER->process_event($this,'');

    if ($this->_default && is_callable($this->_action)) {
		  if (is_array($this->_action)) {
        list($obj,$method) = $this->_action;
        $this->result = $obj->$method($this->data);
			} else {
        $fn = $this->_action;
        $this->result = $fn($this->data);
			}

      $EVENT_HANDLER->process_event($this,'after');
    } 

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

class event_handler {

  // public properties:  none

  // private properties
  var $_hooks = array();          // array of events and their registered handlers

  /*
   * event_handler
   *
   * constructor, loads all action plugins and calls their register() method giving them
   * an opportunity to register any hooks they require
   */
  function event_handler() {

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
  function register_hook($event, &$obj, $method, $param) {
    $this->_hooks[$event][] = array($obj, $method, $param);
  }

  function process_event(&$event,$advise='') {

    $evt_name = $event->name . ($advise ? '_'.$advise : '');

    if (!empty($this->_hooks[$evt_name])) {
      $hook = reset($this->_hooks[$evt_name]);
      do {
        list($obj, $method, $param) = $hook;
        if (is_null($obj)) {
          $method($param, $event);
        } else {
          $obj->$method($param, $event);
        }

      } while ($event->_continue && $hook = next($this->_hooks[$evt_name]));
    }

    return $event->_default;
  }
}

// create the event handler
$EVENT_HANDLER = new event_handler();
