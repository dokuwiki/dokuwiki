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
    /** @var  helper_plugin_extension_gui */
    protected $gui;

    /**
     * Constructor
     *
     * loads additional helpers
     */
    public function __construct() {
        $this->gui = plugin_load('helper', 'extension_gui');
    }

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

        if(!$repository->hasAccess()) {
            $url = $this->gui->tabURL('', array('purge' => 1));
            msg($this->getLang('repo_error').' [<a href="'.$url.'">'.$this->getLang('repo_retry').'</a>]', -1);
        }

        /* @var helper_plugin_extension_extension $extension */
        $extension = $this->loadHelper('extension_extension');

        try {
            if($INPUT->post->has('fn') && checkSecurityToken()) {
                $actions = $INPUT->post->arr('fn');
                foreach($actions as $action => $extensions) {
                    foreach($extensions as $extname => $label) {
                        switch($action) {
                            case 'install':
                            case 'reinstall':
                            case 'update':
                                $extension->setExtension($extname);
                                $installed = $extension->installOrUpdate();
                                foreach($installed as $ext => $info) {
                                    msg(sprintf($this->getLang('msg_'.$info['type'].'_'.$info['action'].'_success'), $info['base']), 1);
                                }
                                break;
                            case 'uninstall':
                                $extension->setExtension($extname);
                                $status = $extension->uninstall();
                                if($status !== true) {
                                    msg($status, -1);
                                } else {
                                    msg(sprintf($this->getLang('msg_delete_success'), hsc($extension->getDisplayName())), 1);
                                }
                                break;
                            case 'enable';
                                $extension->setExtension($extname);
                                $status = $extension->enable();
                                if($status !== true) {
                                    msg($status, -1);
                                } else {
                                    msg(sprintf($this->getLang('msg_enabled'), hsc($extension->getDisplayName())), 1);
                                }
                                break;
                            case 'disable';
                                $extension->setExtension($extname);
                                $status = $extension->disable();
                                if($status !== true) {
                                    msg($status, -1);
                                } else {
                                    msg(sprintf($this->getLang('msg_disabled'), hsc($extension->getDisplayName())), 1);
                                }
                                break;
                        }
                    }
                }
                send_redirect($this->gui->tabURL('', array(), '&', true));
            } elseif($INPUT->post->str('installurl') && checkSecurityToken()) {
                $installed = $extension->installFromURL($INPUT->post->str('installurl'));
                foreach($installed as $ext => $info) {
                    msg(sprintf($this->getLang('msg_'.$info['type'].'_'.$info['action'].'_success'), $info['base']), 1);
                }
                send_redirect($this->gui->tabURL('', array(), '&', true));
            } elseif(isset($_FILES['installfile']) && checkSecurityToken()) {
                $installed = $extension->installFromUpload('installfile');
                foreach($installed as $ext => $info) {
                    msg(sprintf($this->getLang('msg_'.$info['type'].'_'.$info['action'].'_success'), $info['base']), 1);
                }
                send_redirect($this->gui->tabURL('', array(), '&', true));
            }

        } catch(Exception $e) {
            msg($e->getMessage(), -1);
            send_redirect($this->gui->tabURL('', array(), '&', true));
        }

    }

    /**
     * Render HTML output
     */
    public function html() {
        ptln('<h1>'.$this->getLang('menu').'</h1>');
        ptln('<div id="extension__manager">');

        $this->gui->tabNavigation();

        switch($this->gui->currentTab()) {
            case 'search':
                $this->gui->tabSearch();
                break;
            case 'templates':
                $this->gui->tabTemplates();
                break;
            case 'install':
                $this->gui->tabInstall();
                break;
            case 'plugins':
            default:
                $this->gui->tabPlugins();
        }

        ptln('</div>');
    }
}

// vim:ts=4:sw=4:et: