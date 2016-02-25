<?php
/**
 * DokuWiki Plugin struct (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class action_plugin_struct_autoloader extends DokuWiki_Action_Plugin {

    /**
     * Register our own autoloader
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {
        spl_autoload_register(array('action_plugin_struct_autoloader', 'autoloader'));
    }

    /**
     * An SPL auto loader function to load our own classes
     *
     * @param string $name class to be loaded
     * @return bool
     */
    static public function autoloader($name) {
        $name = str_replace('\\', '/', $name);
        if(substr($name, 0, 14) == 'plugin/struct/') {
            /** @noinspection PhpIncludeInspection */
            require DOKU_PLUGIN . substr($name, 7) . '.php';
            return true;
        }
        return false;
    }
}

// vim:ts=4:sw=4:et:
