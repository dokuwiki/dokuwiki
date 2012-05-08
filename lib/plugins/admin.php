<?php
/**
 * Admin Plugin Prototype
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 */
abstract class DokuWiki_Admin_Plugin extends DokuWiki_Plugin {

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

    abstract function handle();

    abstract function html();

    function forAdminOnly() {
        return true;
    }

    function getTOC(){
        return array();
    }
}
//Setup VIM: ex: et ts=4 :
