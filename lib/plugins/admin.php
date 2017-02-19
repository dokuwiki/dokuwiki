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
class DokuWiki_Admin_Plugin extends DokuWiki_Plugin {

    /**
     * Return the text that is displayed at the main admin menu
     * (Default localized language string 'menu' is returned, override this function for setting another name)
     *
     * @param string $language language code
     * @return string menu string
     */
    public function getMenuText($language) {
        $menutext = $this->getLang('menu');
        if (!$menutext) {
            $info = $this->getInfo();
            $menutext = $info['name'].' ...';
        }
        return $menutext;
    }

    /**
     * Return the path to the icon being displayed in the main admin menu.
     * By default it tries to find an 'admin.svg' file in the plugin directory.
     * (Override this function for setting another image)
     *
     * Important: you have to return a single path, monochrome SVG icon! It has to be
     * under 2 Kilobytes!
     *
     * We recommend icons from https://materialdesignicons.com/ or to use a matching
     * style.
     *
     * @return string full path to the icon file
     */
    public function getMenuIcon() {
        $plugin = $this->getPluginName();
        return DOKU_PLUGIN . $plugin . '/admin.svg';
    }

    /**
     * Determine position in list in admin window
     * Lower values are sorted up
     *
     * @return int
     */
    public function getMenuSort() {
        return 1000;
    }

    /**
     * Carry out required processing
     */
    public function handle() {
        trigger_error('handle() not implemented in '.get_class($this), E_USER_WARNING);
    }

    /**
     * Output html of the admin page
     */
    public function html() {
        trigger_error('html() not implemented in '.get_class($this), E_USER_WARNING);
    }

    /**
     * Return true for access only by admins (config:superuser) or false if managers are allowed as well
     *
     * @return bool
     */
    public function forAdminOnly() {
        return true;
    }

    /**
     * Return array with ToC items. Items can be created with the html_mktocitem()
     *
     * @see html_mktocitem()
     * @see tpl_toc()
     *
     * @return array
     */
    public function getTOC(){
        return array();
    }
}
//Setup VIM: ex: et ts=4 :
