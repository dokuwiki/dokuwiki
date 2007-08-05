<?php
/**
 * Admin Plugin Prototype
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_INC.'inc/plugin.php');

/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 */
class DokuWiki_Admin_Plugin extends DokuWiki_Plugin {

    function getMenuText($language) {
        $menutext = $this->getLang('menu');
        if (!$menutext) {
            $info = $this->getInfo();
            $menutext = $info['name'].' ...';
        }
        return $menutext;
    }

    function getMenuSort() {
        return 1000;
    }

    function handle() {
        trigger_error('handle() not implemented in '.get_class($this), E_USER_WARNING);
    }

    function html() {
        trigger_error('html() not implemented in '.get_class($this), E_USER_WARNING);
    }

    function forAdminOnly() {
        return true;
    }

    function getTOC(){
        return array();
    }
}
//Setup VIM: ex: et ts=4 enc=utf-8 :
