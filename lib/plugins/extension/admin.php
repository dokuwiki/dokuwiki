<?php

use dokuwiki\Extension\AdminPlugin;
use dokuwiki\plugin\extension\Exception as RepoException;
use dokuwiki\plugin\extension\Extension;
use dokuwiki\plugin\extension\Gui;
use dokuwiki\plugin\extension\Installer;
use dokuwiki\plugin\extension\Repository;

/**
 * DokuWiki Plugin extension (Admin Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michael Hamann <michael@content-space.de>
 */

/**
 * Admin part of the extension manager
 */
class admin_plugin_extension extends AdminPlugin
{
    protected $infoFor;
    /** @var  helper_plugin_extension_gui */
    protected $gui;

    /**
     * Constructor
     *
     * loads additional helpers
     */
    public function __construct()
    {
        $this->gui = plugin_load('helper', 'extension_gui');
    }

    /**
     * @return int sort number in admin menu
     */
    public function getMenuSort()
    {
        return 0;
    }

    /**
     * @return bool true if only access for superuser, false is for superusers and moderators
     */
    public function forAdminOnly()
    {
        return true;
    }

    /**
     * Execute the requested action(s) and initialize the plugin repository
     */
    public function handle()
    {
        global $INPUT;

        // check access to the repository and SSL support
        $repo = Repository::getInstance();
        try {
            $repo->checkAccess();
        } catch (RepoException $e) {
            msg($e->getMessage(), -1);
        }

        // Only continue if there is something to do
        if (!$INPUT->post->has('fn') && !$INPUT->post->str('installurl') && !isset($_FILES['installfile'])) {
            return; // nothing to do
        }
        if (!checkSecurityToken()) return;

        // Run actions on the installer
        $installer = new Installer($INPUT->post->bool('overwrite'));
        try {
            foreach ($INPUT->post->arr('fn') as $action => $extensions) {
                foreach ($extensions as $extension => $label) {
                    $ext = Extension::createFromId($extension);
                    switch ($action) {
                        case 'install':
                        case 'reinstall':
                        case 'update':
                            $installer->installExtension($ext);
                            break;
                        case 'uninstall':
                            $installer->uninstall($ext);
                            break;
                        case 'enable':
                            $ext->enable();
                            break;
                        case 'disable':
                            $ext->disable();
                            break;
                    }
                }
            }
            if ($INPUT->post->str('installurl')) {
                $installer->installFromURL($INPUT->post->str('installurl'));
            }
            if (isset($_FILES['installfile'])) {
                $installer->installFromUpload('installfile');
            }
        } catch (Exception $e) {
            msg(hsc($e->getMessage()), -1);
        }

        // Report results of the installer
        $processed = $installer->getProcessed();
        foreach ($processed as $id => $status) {
            if ($status == Installer::STATUS_INSTALLED) {
                msg(sprintf($this->getLang('msg_install_success'), $id), 1);
            } else if ($status == Installer::STATUS_UPDATED) {
                msg(sprintf($this->getLang('msg_update_success'), $id), 1);
            } else if ($status == Installer::STATUS_SKIPPED) {
                msg(sprintf($this->getLang('msg_nooverwrite'), $id), 0);
            } else if ($status == Installer::STATUS_REMOVED) {
                msg(sprintf($this->getLang('msg_delete_success'), $id), 1);
            }
        }

        send_redirect((new Gui())->tabURL('', [], '&', true));
    }

    /**
     * Render HTML output
     */
    public function html()
    {
        echo '<h1>' . $this->getLang('menu') . '</h1>';

        $gui = new \dokuwiki\plugin\extension\GuiAdmin();
        echo $gui->render();
    }
}

// vim:ts=4:sw=4:et:
