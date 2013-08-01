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
    }

    /**
     * If the extension is installed locally
     *
     * @return bool If the extension is installed locally
     */
    public function isInstalled() {
    }

    /**
     * If the extension should be updated, i.e. if an updated version is available
     *
     * @return bool If an update is available
     */
    public function updateAvailable() {
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
    }

    /**
     * Get the display name of the extension
     *
     * @return string The display name
     */
    public function getName() {
    }

    /**
     * Get the author name of the extension
     *
     * @return string The name of the author
     */
    public function getAuthor() {
    }

    /**
     * Get the email of the author of the extension
     *
     * @return string The email address
     */
    public function getEmail() {
    }

    /**
     * Get the description of the extension
     *
     * @return string The description
     */
    public function getDescription() {
    }

    /**
     * Get the URL of the extension, usually a page on dokuwiki.org
     *
     * @return string The URL
     */
    public function getURL() {
    }

    /**
     * Get the installed version of the extension
     *
     * @return string The version, usually in the form yyyy-mm-dd
     */
    public function getInstalledVersion() {
    }

    /**
     * Get the names of the dependencies of this extension
     *
     * @return array The base names of the dependencies
     */
    public function getDependencies() {
    }

    /**
     * Get the names of all conflicting extensions
     *
     * @return array The names of the conflicting extensions
     */
    public function getConflicts() {
    }

    /**
     * Get the names of similar extensions
     *
     * @return array The names of similar extensions
     */
    public function getSimilarPlugins() {
    }

    /**
     * Get the names of the tags of the extension
     *
     * @return array The names of the tags of the extension
     */
    public function getTags() {
    }

    /**
     * Get the text of the security warning if there is any
     *
     * @return string|bool The security warning if there is any, false otherwise
     */
    public function getSecurityWarning() {
    }

    /**
     * Get the text of the security issue if there is any
     *
     * @return string|bool The security issue if there is any, false otherwise
     */
    public function getSecurityIssue() {
    }

    /**
     * Get the URL of the screenshot of the extension if there is any
     *
     * @return string|bool The screenshot URL if there is any, false otherwise
     */
    public function getScreenshotURL() {
    }

    /**
     * Get the last used download URL of the extension if there is any
     *
     * @return string|bool The previously used download URL, false if the extension has been installed manually
     */
    public function getLastDownloadURL() {
    }

    /**
     * Get the download URL of the extension if there is any
     *
     * @return string|bool The download URL if there is any, false otherwise
     */
    public function getDownloadURL() {
    }

    /**
     * Get the bug tracker URL of the extension if there is any
     *
     * @return string|bool The bug tracker URL if there is any, false otherwise
     */
    public function getBugtrackerURL() {
    }

    /**
     * Get the URL of the source repository if there is any
     *
     * @return string|bool The URL of the source repository if there is any, false otherwise
     */
    public function getSourcerepoURL() {
    }

    /**
     * Get the donation URL of the extension if there is any
     *
     * @return string|bool The donation URL if there is any, false otherwise
     */
    public function getDonationURL() {
    }

    /**
     * Get the extension type(s)
     *
     * @return array The type(s) as array of strings
     */
    public function getType() {
    }

    /**
     * Get a list of all DokuWiki versions this extension is compatible with
     *
     * @return array The versions in the form yyyy-mm-dd
     */
    public function getCompatibleVersions() {
    }

    /**
     * Get the date of the last available update
     *
     * @return string The last available update in the form yyyy-mm-dd
     */
    public function getLastUpdate() {
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
    public function deleteExtension() {
    }
}

// vim:ts=4:sw=4:et:
