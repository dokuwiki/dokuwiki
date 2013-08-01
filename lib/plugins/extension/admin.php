<?php
/**
 * DokuWiki Plugin extension (Admin Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michael Hamann <michael@content-space.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class admin_plugin_extension extends DokuWiki_Admin_Plugin {

    /**
     * @return int sort number in admin menu
     */
    public function getMenuSort() {
        return 0;
    }

    /**
     * @return bool true if only access for superuser, false is for superusers and moderators
     */
    public function forAdminOnly() {
        return true;
    }

    /**
     * Should carry out any processing required by the plugin.
     */
    public function handle() {
        /* @var helper_plugin_extension_repository $repository */
        $repository = $this->loadHelper('extension_repository');
        $repository->init();
    }

    /**
     * Render HTML output, e.g. helpful text and a form
     */
    public function html() {
        /* @var Doku_Plugin_Controller $plugin_controller */
        global $plugin_controller;
        ptln('<h1>'.$this->getLang('menu').'</h1>');

        $pluginlist = $plugin_controller->getList('', true);
        /* @var helper_plugin_extension_extension $extension */
        $extension = $this->loadHelper('extension_extension');
        foreach ($pluginlist as $name) {
            $extension->setExtension($name, false);
            ptln('<h2>'.hsc($extension->getName()).'</h2>');
            ptln('<p>'.hsc($extension->getDescription()).'</p>');
            ptln('<p>Latest available version: '.hsc($extension->getLastUpdate()).'</p>');
            ptln('<p>Installed version: '.hsc($extension->getInstalledVersion()).'</p>');
        }
    }
}

// vim:ts=4:sw=4:et: