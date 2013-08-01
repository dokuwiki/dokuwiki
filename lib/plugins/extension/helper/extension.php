<?php
/**
 * DokuWiki Plugin extension (Helper Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michael Hamann <michael@content-space.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * Class helper_plugin_extension_extension represents a single extension (plugin or template)
 */
class helper_plugin_extension_extension extends DokuWiki_Plugin {
    private $name;
    private $is_template;
    private $localInfo;
    private $remoteInfo;
    private $managerData;
    /** @var helper_plugin_extension_repository $repository */
    private $repository = null;

    /**
     * @return bool false, this component is not a singleton
     */
    public function isSingleton() {
        return false;
    }

    /**
     * Set the name of the extension this instance shall represents, triggers loading the local and remote data
     *
     * @param string $name        The base name of the extension
     * @param bool   $is_template If the extension is a template
     * @return bool If some (local or remote) data was found
     */
    public function setExtension($name, $is_template) {
        $this->name = $name;
        $this->is_template = $is_template;
        $this->localInfo = array();
        $this->managerData = array();
        $this->remoteInfo = array();

        if ($this->isInstalled()) {
            if ($this->isTemplate()) {
                $infopath = $this->getInstallDir().'/template.info.txt';
            } else {
                $infopath = $this->getInstallDir().'/plugin.info.txt';
            }
            if (is_readable($infopath)) {
                $this->localInfo = confToHash($infopath);
            }

            $this->readManagerData();
        }

        if ($this->repository == null) {
            $this->repository = $this->loadHelper('extension_repository');
        }

        $this->remoteInfo = $this->repository->getData(($this->isTemplate() ? 'template:' : '').$this->getBase());
    }

    /**
     * If the extension is installed locally
     *
     * @return bool If the extension is installed locally
     */
    public function isInstalled() {
        return is_dir($this->getInstallDir());
    }

    /**
     * If the extension is enabled
     *
     * @return bool If the extension is enabled
     */
    public function isEnabled() {
        /* @var Doku_Plugin_Controller $plugin_controller */
        global $plugin_controller;
        return !$plugin_controller->isdisabled($this->name);
    }

    /**
     * If the extension should be updated, i.e. if an updated version is available
     *
     * @return bool If an update is available
     */
    public function updateAvailable() {
        $lastupdate = $this->getLastUpdate();
        if ($lastupdate === false) return false;
        return $this->getInstalledVersion() < $this->getLastUpdate();
    }

    /**
     * If the extension is a template
     *
     * @return bool If this extension is a template
     */
    public function isTemplate() {
        return $this->is_template;
    }

    // Data from plugin.info.txt/template.info.txt or the repo when not available locally
    /**
     * Get the basename of the extension
     *
     * @return string The basename
     */
    public function getBase() {
        return $this->name;
    }

    /**
     * Get the display name of the extension
     *
     * @return string The display name
     */
    public function getName() {
        if (isset($this->localInfo['name'])) return $this->localInfo['name'];
        if (isset($this->remoteInfo['name'])) return $this->remoteInfo['name'];
        return $this->name;
    }

    /**
     * Get the author name of the extension
     *
     * @return string The name of the author
     */
    public function getAuthor() {
        if (isset($this->localInfo['author'])) return $this->localInfo['author'];
        if (isset($this->remoteInfo['author'])) return $this->remoteInfo['author'];
        return $this->getLang('unknownauthor');
    }

    /**
     * Get the email of the author of the extension if there is any
     *
     * @return string|bool The email address or false if there is none
     */
    public function getEmail() {
        // email is only in the local data
        if (isset($this->localInfo['email'])) return $this->localInfo['email'];
        return false;
    }

    /**
     * Get the email id, i.e. the md5sum of the email
     *
     * @return string|bool The md5sum of the email if there is any, false otherwise
     */
    public function getEmailID() {
        if (isset($this->remoteInfo['emailid'])) return $this->remoteInfo['emailid'];
        if (isset($this->localInfo['email'])) return md5($this->localInfo['email']);
        return false;
    }

    /**
     * Get the description of the extension
     *
     * @return string The description
     */
    public function getDescription() {
        if (isset($this->localInfo['desc'])) return $this->localInfo['desc'];
        if (isset($this->remoteInfo['description'])) return $this->remoteInfo['description'];
        return '';
    }

    /**
     * Get the URL of the extension, usually a page on dokuwiki.org
     *
     * @return string The URL
     */
    public function getURL() {
        if (isset($this->localInfo['url'])) return $this->localInfo['url'];
        return 'https://www.dokuwiki.org/plugin:'.$this->name;
    }

    /**
     * Get the installed version of the extension
     *
     * @return string|bool The version, usually in the form yyyy-mm-dd if there is any
     */
    public function getInstalledVersion() {
        if (isset($this->localInfo['date'])) return $this->localInfo['date'];
        if ($this->isInstalled()) return $this->getLang('unknownversion');
        return false;
    }

    /**
     * Get the names of the dependencies of this extension
     *
     * @return array The base names of the dependencies
     */
    public function getDependencies() {
        if (isset($this->remoteInfo['dependencies'])) return $this->remoteInfo['dependencies'];
        return array();
    }

    /**
     * Get the names of all conflicting extensions
     *
     * @return array The names of the conflicting extensions
     */
    public function getConflicts() {
        if (isset($this->remoteInfo['conflicts'])) return $this->remoteInfo['dependencies'];
        return array();
    }

    /**
     * Get the names of similar extensions
     *
     * @return array The names of similar extensions
     */
    public function getSimilarExtensions() {
        if (isset($this->remoteInfo['similar'])) return $this->remoteInfo['similar'];
        return array();
    }

    /**
     * Get the names of the tags of the extension
     *
     * @return array The names of the tags of the extension
     */
    public function getTags() {
        if (isset($this->remoteInfo['tags'])) return $this->remoteInfo['tags'];
        return array();
    }

    /**
     * Get the text of the security warning if there is any
     *
     * @return string|bool The security warning if there is any, false otherwise
     */
    public function getSecurityWarning() {
        if (isset($this->remoteInfo['securitywarning'])) return $this->remoteInfo['securitywarning'];
        return false;
    }

    /**
     * Get the text of the security issue if there is any
     *
     * @return string|bool The security issue if there is any, false otherwise
     */
    public function getSecurityIssue() {
        if (isset($this->remoteInfo['securityissue'])) return $this->remoteInfo['securityissue'];
        return false;
    }

    /**
     * Get the URL of the screenshot of the extension if there is any
     *
     * @return string|bool The screenshot URL if there is any, false otherwise
     */
    public function getScreenshotURL() {
        if (isset($this->remoteInfo['screenshoturl'])) return $this->remoteInfo['screenshoturl'];
        return false;
    }

    /**
     * Get the last used download URL of the extension if there is any
     *
     * @return string|bool The previously used download URL, false if the extension has been installed manually
     */
    public function getLastDownloadURL() {
        if (isset($this->managerData['downloadurl'])) return $this->managerData['downloadurl'];
        return false;
    }

    /**
     * Get the download URL of the extension if there is any
     *
     * @return string|bool The download URL if there is any, false otherwise
     */
    public function getDownloadURL() {
        if (isset($this->remoteInfo['downloadurl'])) return $this->remoteInfo['downloadurl'];
        return false;
    }

    /**
     * Get the bug tracker URL of the extension if there is any
     *
     * @return string|bool The bug tracker URL if there is any, false otherwise
     */
    public function getBugtrackerURL() {
        if (isset($this->remoteInfo['bugtracker'])) return $this->remoteInfo['bugtracker'];
        return false;
    }

    /**
     * Get the URL of the source repository if there is any
     *
     * @return string|bool The URL of the source repository if there is any, false otherwise
     */
    public function getSourcerepoURL() {
        if (isset($this->remoteInfo['sourcerepo'])) return $this->remoteInfo['sourcerepo'];
        return false;
    }

    /**
     * Get the donation URL of the extension if there is any
     *
     * @return string|bool The donation URL if there is any, false otherwise
     */
    public function getDonationURL() {
        if (isset($this->remoteInfo['donationurl'])) return $this->remoteInfo['donationurl'];
        return false;
    }

    /**
     * Get the extension type(s)
     *
     * @return array The type(s) as array of strings
     */
    public function getTypes() {
        if (isset($this->remoteInfo['types'])) return explode(', ', $this->remoteInfo['types']);
        if ($this->isTemplate()) return array(32 => 'template');
        return array();
    }

    /**
     * Get a list of all DokuWiki versions this extension is compatible with
     *
     * @return array The versions in the form yyyy-mm-dd => ('label' => label, 'implicit' => implicit)
     */
    public function getCompatibleVersions() {
        if (isset($this->remoteInfo['compatible'])) return $this->remoteInfo['compatible'];
        return array();
    }

    /**
     * Get the date of the last available update
     *
     * @return string|bool The last available update in the form yyyy-mm-dd if there is any, false otherwise
     */
    public function getLastUpdate() {
        if (isset($this->remoteInfo['lastupdate'])) return $this->remoteInfo['lastupdate'];
        return false;
    }

    /**
     * Get the base path of the extension
     *
     * @return string The base path of the extension
     */
    public function getInstallDir() {
        if ($this->isTemplate()) {
            return basename(tpl_incdir()).$this->name;
        } else {
            return DOKU_PLUGIN.$this->name;
        }
    }

    /**
     * The type of extension installation
     *
     * @return string One of "none", "manual", "git" or "automatic"
     */
    public function getInstallType() {
        if (!$this->isInstalled()) return 'none';
        if (!empty($this->managerData)) return 'automatic';
        if (is_dir($this->getInstallDir().'/.git')) return 'git';
        return 'manual';
    }

    /**
     * If the extension can probably be installed/updated or uninstalled
     *
     * @return bool|string True or one of "nourl", "noparentperms" (template/plugin install path not writable), "noperms" (extension itself not writable)
     */
    public function canModify() {
    }

    /**
     * Install or update the extension
     *
     * @return bool|string True or an error message
     */
    public function installOrUpdate() {
    }

    /**
     * Uninstall the extension
     *
     * @return bool|string True or an error message
     */
    public function uninstall() {
    }

    /**
     * Enable the extension
     *
     * @return bool|string True or an error message
     */
    public function enable() {
        if ($this->isTemplate()) return $this->getLang('notimplemented');
        /* @var Doku_Plugin_Controller $plugin_controller */
        global $plugin_controller;
        if (!$this->isInstalled()) return $this->getLang('notinstalled');
        if (!$this->isEnabled()) return $this->getLang('alreadyenabled');
        if ($plugin_controller->enable($this->name)) {
            return true;
        } else {
            return $this->getLang('pluginlistsaveerror');
        }
    }

    /**
     * Disable the extension
     *
     * @return bool|string True or an error message
     */
    public function disable() {
        if ($this->isTemplate()) return $this->getLang('notimplemented');

        /* @var Doku_Plugin_Controller $plugin_controller */
        global $plugin_controller;
        if (!$this->isInstalled()) return $this->getLang('notinstalled');
        if (!$this->isEnabled()) return $this->getLang('alreadydisabled');
        if ($plugin_controller->disable($this->name)) {
            return true;
        } else {
            return $this->getLang('pluginlistsaveerror');
        }
    }

    /**
     * Read the manager.dat file
     */
    protected function readManagerData() {
        $managerpath = $this->getInstallDir().'/manager.dat';
        if (is_readable($managerpath)) {
            $file = @file($managerpath);
            if(!empty($file)) {
                foreach($file as $line) {
                    list($key, $value) = explode('=', trim($line, PHP_EOL), 2);
                    $key = trim($key);
                    $value = trim($value);
                    // backwards compatible with old plugin manager
                    if($key == 'url') $key = 'downloadurl';
                    $this->managerData[$key] = $value;
                }
            }
        }
    }

    /**
     * Write the manager.data file
     */
    protected function writeManagerData() {
        $managerpath = $this->getInstallDir().'/manager.dat';
        $data = '';
        foreach ($this->managerData as $k => $v) {
            $data .= $k.'='.$v.DOKU_LF;
        }
        io_saveFile($managerpath, $data);
    }
}

// vim:ts=4:sw=4:et:
