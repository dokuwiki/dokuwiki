<?php
/**
 * Auth Plugin Prototype
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Jan Schumann <js@jschumann-it.com>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * All plugins that provide Authentication should inherit from this class and implement
 * the getAuth() method to make its Auth-System available.
 *
 * @author Jan Schumann <js@jschumann-it.com>
 */
class DokuWiki_Auth_Plugin extends DokuWiki_Plugin {

	/**
     * Retrieves the authentication system
     */
	function getAuth() {
     	trigger_error('getAuth() not implemented in '.get_class($this), E_USER_WARNING);
    }
}
