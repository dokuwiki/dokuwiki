<?php
/**
 * Action Plugin Prototype
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_INC.'inc/plugin.php');

/**
 * All DokuWiki plugins to interfere with the event system
 * need to inherit from this class
 */
class DokuWiki_Action_Plugin extends DokuWiki_Plugin {

     /**
      * Registers a callback function for a given event
      */
      function register($controller) {
        trigger_error('register() not implemented in '.get_class($this), E_USER_WARNING);
      }
}
