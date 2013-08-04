<?php
/**
 * DokuWiki Plugin extension (Admin Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michael Hamann <michael@content-space.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * Admin part of the extension manager
 */
class admin_plugin_extension extends DokuWiki_Admin_Plugin {
    protected $infoFor = null;

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
     * Execute the requested action(s) and initialize the plugin repository
     */
    public function handle() {
        global $INPUT;
        // initialize the remote repository
        /* @var helper_plugin_extension_repository $repository */
        $repository = $this->loadHelper('extension_repository');
        $repository->init();

        if(!$repository->hasAccess()){
            $url = helper_plugin_extension_list::tabURL('', array('purge'=>1));

            msg('The DokuWiki extension repository can not be reached currently.
                 Online Features are not available. [<a href="'.$url.'">retry</a>]', -1);
        }

        /* @var helper_plugin_extension_extension $extension */
        $extension = $this->loadHelper('extension_extension');

        if ($INPUT->post->has('fn')) {
            $actions = $INPUT->post->arr('fn');
            foreach ($actions as $action => $extensions) {
                foreach ($extensions as $extname => $label) {
                    switch ($action) {
                        case 'info':
                            $this->infoFor = $extname;
                            break;
                        case 'install':
                            msg('Not implemented');
                            break;
                        case 'reinstall':
                        case 'update':
                            $extension->setExtension($extname, false);
                            $status = $extension->installOrUpdate();
                            if ($status !== true) {
                                msg($status, -1);
                            } else {
                                msg(sprintf($this->getLang('msg_update_success'), hsc($extension->getName())), 1);
                            }
                            break;
                        case 'uninstall':
                            $extension->setExtension($extname, false);
                            $status = $extension->uninstall();
                            if ($status !== true) {
                                msg($status, -1);
                            } else {
                                msg(sprintf($this->getLang('msg_delete_success'), hsc($extension->getName())), 1);
                            }
                            break;
                        case 'enable';
                            $extension->setExtension($extname, false);
                            $status = $extension->enable();
                            if ($status !== true) {
                                msg($status, -1);
                            } else {
                                msg(sprintf($this->getLang('msg_enabled'), hsc($extension->getName())), 1);
                            }
                            break;
                        case 'disable';
                            $extension->setExtension($extname, false);
                            $status = $extension->disable();
                            if ($status !== true) {
                                msg($status, -1);
                            } else {
                                msg(sprintf($this->getLang('msg_disabled'), hsc($extension->getName())), 1);
                            }
                            break;
                    }
                }
            }
        }
    }

    /**
     * Render HTML output
     */
    public function html() {
        /* @var Doku_Plugin_Controller $plugin_controller */
        global $plugin_controller;
        ptln('<h1>'.$this->getLang('menu').'</h1>');
        ptln('<div id="extension__manager">');

        $pluginlist = $plugin_controller->getList('', true);
        /* @var helper_plugin_extension_extension $extension */
        $extension = $this->loadHelper('extension_extension');
        /* @var helper_plugin_extension_list $list */
        $list = $this->loadHelper('extension_list');
        $list->start_form();
        foreach ($pluginlist as $name) {
            $extension->setExtension($name, false);
            $list->add_row($extension, $name == $this->infoFor);
        }
        $list->end_form();
        $list->render();
        ptln('</div>');
    }
}

// vim:ts=4:sw=4:et: