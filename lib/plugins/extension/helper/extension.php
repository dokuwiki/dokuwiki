<?php

/**
 * DokuWiki Plugin extension (Helper Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michael Hamann <michael@content-space.de>
 */

use dokuwiki\Extension\Plugin;
use dokuwiki\Extension\PluginInterface;
use dokuwiki\Utf8\PhpString;
use splitbrain\PHPArchive\Tar;
use splitbrain\PHPArchive\ArchiveIOException;
use splitbrain\PHPArchive\Zip;
use dokuwiki\HTTP\DokuHTTPClient;
use dokuwiki\Extension\PluginController;

/**
 * Class helper_plugin_extension_extension represents a single extension (plugin or template)
 */
class helper_plugin_extension_extension extends Plugin
{
    private $id;
    private $base;
    private $is_template = false;
    private $localInfo;
    private $remoteInfo;
    private $managerData;
    /** @var helper_plugin_extension_repository $repository */
    private $repository;

    /** @var array list of temporary directories */
    private $temporary = [];

    /** @var string where templates are installed to */
    private $tpllib = '';

    /**
     * helper_plugin_extension_extension constructor.
     */
    public function __construct()
    {
        $this->tpllib = dirname(tpl_incdir()) . '/';
    }

    /**
     * Destructor
     *
     * deletes any dangling temporary directories
     */
    public function __destruct()
    {
        foreach ($this->temporary as $dir) {
            io_rmdir($dir, true);
        }
    }

    /**
     * @return bool false, this component is not a singleton
     */
    public function isSingleton()
    {
        return false;
    }

    /**
     * Set the name of the extension this instance shall represents, triggers loading the local and remote data
     *
     * @param string $id  The id of the extension (prefixed with template: for templates)
     * @return bool If some (local or remote) data was found
     */
    public function setExtension($id)
    {
        $id = cleanID($id);
        $this->id   = $id;

        $this->base = $id;

        if (str_starts_with($id, 'template:')) {
            $this->base = substr($id, 9);
            $this->is_template = true;
        } else {
            $this->is_template = false;
        }

        $this->localInfo = [];
        $this->managerData = [];
        $this->remoteInfo = [];

        if ($this->isInstalled()) {
            $this->readLocalData();
            $this->readManagerData();
        }

        if ($this->repository == null) {
            $this->repository = $this->loadHelper('extension_repository');
        }

        $this->remoteInfo = $this->repository->getData($this->getID());

        return ($this->localInfo || $this->remoteInfo);
    }

    /**
     * If the extension is installed locally
     *
     * @return bool If the extension is installed locally
     */
    public function isInstalled()
    {
        return is_dir($this->getInstallDir());
    }

    /**
     * If the extension is under git control
     *
     * @return bool
     */
    public function isGitControlled()
    {
        if (!$this->isInstalled()) return false;
        return file_exists($this->getInstallDir() . '/.git');
    }

    /**
     * If the extension is bundled
     *
     * @return bool If the extension is bundled
     */
    public function isBundled()
    {
        if (!empty($this->remoteInfo['bundled'])) return $this->remoteInfo['bundled'];
        return in_array(
            $this->id,
            [
                'authad',
                'authldap',
                'authpdo',
                'authplain',
                'acl',
                'config',
                'extension',
                'info',
                'popularity',
                'revert',
                'safefnrecode',
                'styling',
                'testing',
                'usermanager',
                'logviewer',
                'template:dokuwiki'
            ]
        );
    }

    /**
     * If the extension is protected against any modification (disable/uninstall)
     *
     * @return bool if the extension is protected
     */
    public function isProtected()
    {
        // never allow deinstalling the current auth plugin:
        global $conf;
        if ($this->id == $conf['authtype']) return true;

        /** @var PluginController $plugin_controller */
        global $plugin_controller;
        $cascade = $plugin_controller->getCascade();
        return (isset($cascade['protected'][$this->id]) && $cascade['protected'][$this->id]);
    }

    /**
     * If the extension is installed in the correct directory
     *
     * @return bool If the extension is installed in the correct directory
     */
    public function isInWrongFolder()
    {
        return $this->base != $this->getBase();
    }

    /**
     * If the extension is enabled
     *
     * @return bool If the extension is enabled
     */
    public function isEnabled()
    {
        global $conf;
        if ($this->isTemplate()) {
            return ($conf['template'] == $this->getBase());
        }

        /* @var PluginController $plugin_controller */
        global $plugin_controller;
        return $plugin_controller->isEnabled($this->base);
    }

    /**
     * If the extension should be updated, i.e. if an updated version is available
     *
     * @return bool If an update is available
     */
    public function updateAvailable()
    {
        if (!$this->isInstalled()) return false;
        if ($this->isBundled()) return false;
        $lastupdate = $this->getLastUpdate();
        if ($lastupdate === false) return false;
        $installed  = $this->getInstalledVersion();
        if ($installed === false || $installed === $this->getLang('unknownversion')) return true;
        return $this->getInstalledVersion() < $this->getLastUpdate();
    }

    /**
     * If the extension is a template
     *
     * @return bool If this extension is a template
     */
    public function isTemplate()
    {
        return $this->is_template;
    }

    /**
     * Get the ID of the extension
     *
     * This is the same as getName() for plugins, for templates it's getName() prefixed with 'template:'
     *
     * @return string
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * Get the name of the installation directory
     *
     * @return string The name of the installation directory
     */
    public function getInstallName()
    {
        return $this->base;
    }

    // Data from plugin.info.txt/template.info.txt or the repo when not available locally
    /**
     * Get the basename of the extension
     *
     * @return string The basename
     */
    public function getBase()
    {
        if (!empty($this->localInfo['base'])) return $this->localInfo['base'];
        return $this->base;
    }

    /**
     * Get the display name of the extension
     *
     * @return string The display name
     */
    public function getDisplayName()
    {
        if (!empty($this->localInfo['name'])) return $this->localInfo['name'];
        if (!empty($this->remoteInfo['name'])) return $this->remoteInfo['name'];
        return $this->base;
    }

    /**
     * Get the author name of the extension
     *
     * @return string|bool The name of the author or false if there is none
     */
    public function getAuthor()
    {
        if (!empty($this->localInfo['author'])) return $this->localInfo['author'];
        if (!empty($this->remoteInfo['author'])) return $this->remoteInfo['author'];
        return false;
    }

    /**
     * Get the email of the author of the extension if there is any
     *
     * @return string|bool The email address or false if there is none
     */
    public function getEmail()
    {
        // email is only in the local data
        if (!empty($this->localInfo['email'])) return $this->localInfo['email'];
        return false;
    }

    /**
     * Get the email id, i.e. the md5sum of the email
     *
     * @return string|bool The md5sum of the email if there is any, false otherwise
     */
    public function getEmailID()
    {
        if (!empty($this->remoteInfo['emailid'])) return $this->remoteInfo['emailid'];
        if (!empty($this->localInfo['email'])) return md5($this->localInfo['email']);
        return false;
    }

    /**
     * Get the description of the extension
     *
     * @return string The description
     */
    public function getDescription()
    {
        if (!empty($this->localInfo['desc'])) return $this->localInfo['desc'];
        if (!empty($this->remoteInfo['description'])) return $this->remoteInfo['description'];
        return '';
    }

    /**
     * Get the URL of the extension, usually a page on dokuwiki.org
     *
     * @return string The URL
     */
    public function getURL()
    {
        if (!empty($this->localInfo['url'])) return $this->localInfo['url'];
        return 'https://www.dokuwiki.org/' .
            ($this->isTemplate() ? 'template' : 'plugin') . ':' . $this->getBase();
    }

    /**
     * Get the installed version of the extension
     *
     * @return string|bool The version, usually in the form yyyy-mm-dd if there is any
     */
    public function getInstalledVersion()
    {
        if (!empty($this->localInfo['date'])) return $this->localInfo['date'];
        if ($this->isInstalled()) return $this->getLang('unknownversion');
        return false;
    }

    /**
     * Get the install date of the current version
     *
     * @return string|bool The date of the last update or false if not available
     */
    public function getUpdateDate()
    {
        if (!empty($this->managerData['updated'])) return $this->managerData['updated'];
        return $this->getInstallDate();
    }

    /**
     * Get the date of the installation of the plugin
     *
     * @return string|bool The date of the installation or false if not available
     */
    public function getInstallDate()
    {
        if (!empty($this->managerData['installed'])) return $this->managerData['installed'];
        return false;
    }

    /**
     * Get the names of the dependencies of this extension
     *
     * @return array The base names of the dependencies
     */
    public function getDependencies()
    {
        if (!empty($this->remoteInfo['dependencies'])) return $this->remoteInfo['dependencies'];
        return [];
    }

    /**
     * Get the names of the missing dependencies
     *
     * @return array The base names of the missing dependencies
     */
    public function getMissingDependencies()
    {
        /* @var PluginController $plugin_controller */
        global $plugin_controller;
        $dependencies = $this->getDependencies();
        $missing_dependencies = [];
        foreach ($dependencies as $dependency) {
            if (!$plugin_controller->isEnabled($dependency)) {
                $missing_dependencies[] = $dependency;
            }
        }
        return $missing_dependencies;
    }

    /**
     * Get the names of all conflicting extensions
     *
     * @return array The names of the conflicting extensions
     */
    public function getConflicts()
    {
        if (!empty($this->remoteInfo['conflicts'])) return $this->remoteInfo['conflicts'];
        return [];
    }

    /**
     * Get the names of similar extensions
     *
     * @return array The names of similar extensions
     */
    public function getSimilarExtensions()
    {
        if (!empty($this->remoteInfo['similar'])) return $this->remoteInfo['similar'];
        return [];
    }

    /**
     * Get the names of the tags of the extension
     *
     * @return array The names of the tags of the extension
     */
    public function getTags()
    {
        if (!empty($this->remoteInfo['tags'])) return $this->remoteInfo['tags'];
        return [];
    }

    /**
     * Get the popularity information as floating point number [0,1]
     *
     * @return float|bool The popularity information or false if it isn't available
     */
    public function getPopularity()
    {
        if (!empty($this->remoteInfo['popularity'])) return $this->remoteInfo['popularity'];
        return false;
    }

    /**
     * Get the text of the update message if there is any
     *
     * @return string|bool The update message if there is any, false otherwise
     */
    public function getUpdateMessage()
    {
        if (!empty($this->remoteInfo['updatemessage'])) return $this->remoteInfo['updatemessage'];
        return false;
    }

    /**
     * Get the text of the security warning if there is any
     *
     * @return string|bool The security warning if there is any, false otherwise
     */
    public function getSecurityWarning()
    {
        if (!empty($this->remoteInfo['securitywarning'])) return $this->remoteInfo['securitywarning'];
        return false;
    }

    /**
     * Get the text of the security issue if there is any
     *
     * @return string|bool The security issue if there is any, false otherwise
     */
    public function getSecurityIssue()
    {
        if (!empty($this->remoteInfo['securityissue'])) return $this->remoteInfo['securityissue'];
        return false;
    }

    /**
     * Get the URL of the screenshot of the extension if there is any
     *
     * @return string|bool The screenshot URL if there is any, false otherwise
     */
    public function getScreenshotURL()
    {
        if (!empty($this->remoteInfo['screenshoturl'])) return $this->remoteInfo['screenshoturl'];
        return false;
    }

    /**
     * Get the URL of the thumbnail of the extension if there is any
     *
     * @return string|bool The thumbnail URL if there is any, false otherwise
     */
    public function getThumbnailURL()
    {
        if (!empty($this->remoteInfo['thumbnailurl'])) return $this->remoteInfo['thumbnailurl'];
        return false;
    }
    /**
     * Get the last used download URL of the extension if there is any
     *
     * @return string|bool The previously used download URL, false if the extension has been installed manually
     */
    public function getLastDownloadURL()
    {
        if (!empty($this->managerData['downloadurl'])) return $this->managerData['downloadurl'];
        return false;
    }

    /**
     * Get the download URL of the extension if there is any
     *
     * @return string|bool The download URL if there is any, false otherwise
     */
    public function getDownloadURL()
    {
        if (!empty($this->remoteInfo['downloadurl'])) return $this->remoteInfo['downloadurl'];
        return false;
    }

    /**
     * If the download URL has changed since the last download
     *
     * @return bool If the download URL has changed
     */
    public function hasDownloadURLChanged()
    {
        $lasturl = $this->getLastDownloadURL();
        $currenturl = $this->getDownloadURL();
        return ($lasturl && $currenturl && $lasturl != $currenturl);
    }

    /**
     * Get the bug tracker URL of the extension if there is any
     *
     * @return string|bool The bug tracker URL if there is any, false otherwise
     */
    public function getBugtrackerURL()
    {
        if (!empty($this->remoteInfo['bugtracker'])) return $this->remoteInfo['bugtracker'];
        return false;
    }

    /**
     * Get the URL of the source repository if there is any
     *
     * @return string|bool The URL of the source repository if there is any, false otherwise
     */
    public function getSourcerepoURL()
    {
        if (!empty($this->remoteInfo['sourcerepo'])) return $this->remoteInfo['sourcerepo'];
        return false;
    }

    /**
     * Get the donation URL of the extension if there is any
     *
     * @return string|bool The donation URL if there is any, false otherwise
     */
    public function getDonationURL()
    {
        if (!empty($this->remoteInfo['donationurl'])) return $this->remoteInfo['donationurl'];
        return false;
    }

    /**
     * Get the extension type(s)
     *
     * @return array The type(s) as array of strings
     */
    public function getTypes()
    {
        if (!empty($this->remoteInfo['types'])) return $this->remoteInfo['types'];
        if ($this->isTemplate()) return [32 => 'template'];
        return [];
    }

    /**
     * Get a list of all DokuWiki versions this extension is compatible with
     *
     * @return array The versions in the form yyyy-mm-dd => ('label' => label, 'implicit' => implicit)
     */
    public function getCompatibleVersions()
    {
        if (!empty($this->remoteInfo['compatible'])) return $this->remoteInfo['compatible'];
        return [];
    }

    /**
     * Get the date of the last available update
     *
     * @return string|bool The last available update in the form yyyy-mm-dd if there is any, false otherwise
     */
    public function getLastUpdate()
    {
        if (!empty($this->remoteInfo['lastupdate'])) return $this->remoteInfo['lastupdate'];
        return false;
    }

    /**
     * Get the base path of the extension
     *
     * @return string The base path of the extension
     */
    public function getInstallDir()
    {
        if ($this->isTemplate()) {
            return $this->tpllib . $this->base;
        } else {
            return DOKU_PLUGIN . $this->base;
        }
    }

    /**
     * The type of extension installation
     *
     * @return string One of "none", "manual", "git" or "automatic"
     */
    public function getInstallType()
    {
        if (!$this->isInstalled()) return 'none';
        if (!empty($this->managerData)) return 'automatic';
        if (is_dir($this->getInstallDir() . '/.git')) return 'git';
        return 'manual';
    }

    /**
     * If the extension can probably be installed/updated or uninstalled
     *
     * @return bool|string True or error string
     */
    public function canModify()
    {
        if ($this->isInstalled()) {
            if (!is_writable($this->getInstallDir())) {
                return 'noperms';
            }
        }

        if ($this->isTemplate() && !is_writable($this->tpllib)) {
            return 'notplperms';
        } elseif (!is_writable(DOKU_PLUGIN)) {
            return 'nopluginperms';
        }
        return true;
    }

    /**
     * Install an extension from a user upload
     *
     * @param string $field name of the upload file
     * @param boolean $overwrite overwrite folder if the extension name is the same
     * @throws Exception when something goes wrong
     * @return array The list of installed extensions
     */
    public function installFromUpload($field, $overwrite = true)
    {
        if ($_FILES[$field]['error']) {
            throw new Exception($this->getLang('msg_upload_failed') . ' (' . $_FILES[$field]['error'] . ')');
        }

        $tmp = $this->mkTmpDir();
        if (!$tmp) throw new Exception($this->getLang('error_dircreate'));

        // filename may contain the plugin name for old style plugins...
        $basename = basename($_FILES[$field]['name']);
        $basename = preg_replace('/\.(tar\.gz|tar\.bz|tar\.bz2|tar|tgz|tbz|zip)$/', '', $basename);
        $basename = preg_replace('/[\W]+/', '', $basename);

        if (!move_uploaded_file($_FILES[$field]['tmp_name'], "$tmp/upload.archive")) {
            throw new Exception($this->getLang('msg_upload_failed'));
        }
        $installed = $this->installArchive("$tmp/upload.archive", $overwrite, $basename);
        $this->updateManagerData('', $installed);
        $this->removeDeletedfiles($installed);
        $this->purgeCache();
        return $installed;
    }

    /**
     * Install an extension from a remote URL
     *
     * @param string $url
     * @param boolean $overwrite overwrite folder if the extension name is the same
     * @throws Exception when something goes wrong
     * @return array The list of installed extensions
     */
    public function installFromURL($url, $overwrite = true)
    {
        $path      = $this->download($url);
        $installed = $this->installArchive($path, $overwrite);
        $this->updateManagerData($url, $installed);
        $this->removeDeletedfiles($installed);
        $this->purgeCache();
        return $installed;
    }

    /**
     * Install or update the extension
     *
     * @throws \Exception when something goes wrong
     * @return array The list of installed extensions
     */
    public function installOrUpdate()
    {
        $url       = $this->getDownloadURL();
        $path      = $this->download($url);
        $installed = $this->installArchive($path, $this->isInstalled(), $this->getBase());
        $this->updateManagerData($url, $installed);

        // refresh extension information
        if (!isset($installed[$this->getID()])) {
            throw new Exception('Error, the requested extension hasn\'t been installed or updated');
        }
        $this->removeDeletedfiles($installed);
        $this->setExtension($this->getID());
        $this->purgeCache();
        return $installed;
    }

    /**
     * Uninstall the extension
     *
     * @return bool If the plugin was sucessfully uninstalled
     */
    public function uninstall()
    {
        $this->purgeCache();
        return io_rmdir($this->getInstallDir(), true);
    }

    /**
     * Enable the extension
     *
     * @return bool|string True or an error message
     */
    public function enable()
    {
        if ($this->isTemplate()) return $this->getLang('notimplemented');
        if (!$this->isInstalled()) return $this->getLang('notinstalled');
        if ($this->isEnabled()) return $this->getLang('alreadyenabled');

        /* @var PluginController $plugin_controller */
        global $plugin_controller;
        if ($plugin_controller->enable($this->base)) {
            $this->purgeCache();
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
    public function disable()
    {
        if ($this->isTemplate()) return $this->getLang('notimplemented');

        /* @var PluginController $plugin_controller */
        global $plugin_controller;
        if (!$this->isInstalled()) return $this->getLang('notinstalled');
        if (!$this->isEnabled()) return $this->getLang('alreadydisabled');
        if ($plugin_controller->disable($this->base)) {
            $this->purgeCache();
            return true;
        } else {
            return $this->getLang('pluginlistsaveerror');
        }
    }

    /**
     * Purge the cache by touching the main configuration file
     */
    protected function purgeCache()
    {
        global $config_cascade;

        // expire dokuwiki caches
        // touching local.php expires wiki page, JS and CSS caches
        @touch(reset($config_cascade['main']['local']));
    }

    /**
     * Read local extension data either from info.txt or getInfo()
     */
    protected function readLocalData()
    {
        if ($this->isTemplate()) {
            $infopath = $this->getInstallDir() . '/template.info.txt';
        } else {
            $infopath = $this->getInstallDir() . '/plugin.info.txt';
        }

        if (is_readable($infopath)) {
            $this->localInfo = confToHash($infopath);
        } elseif (!$this->isTemplate() && $this->isEnabled()) {
            $path   = $this->getInstallDir() . '/';
            $plugin = null;

            foreach (PluginController::PLUGIN_TYPES as $type) {
                if (file_exists($path . $type . '.php')) {
                    $plugin = plugin_load($type, $this->base);
                    if ($plugin instanceof PluginInterface) break;
                }

                if ($dh = @opendir($path . $type . '/')) {
                    while (false !== ($cp = readdir($dh))) {
                        if ($cp == '.' || $cp == '..' || !str_ends_with(strtolower($cp), '.php')) continue;

                        $plugin = plugin_load($type, $this->base . '_' . substr($cp, 0, -4));
                        if ($plugin instanceof PluginInterface) break;
                    }
                    if ($plugin instanceof PluginInterface) break;
                    closedir($dh);
                }
            }

            if ($plugin instanceof PluginInterface) {
                $this->localInfo = $plugin->getInfo();
            }
        }
    }

    /**
     * Save the given URL and current datetime in the manager.dat file of all installed extensions
     *
     * @param string $url       Where the extension was downloaded from. (empty for manual installs via upload)
     * @param array  $installed Optional list of installed plugins
     */
    protected function updateManagerData($url = '', $installed = null)
    {
        $origID = $this->getID();

        if (is_null($installed)) {
            $installed = [$origID];
        }

        foreach (array_keys($installed) as $ext) {
            if ($this->getID() != $ext) $this->setExtension($ext);
            if ($url) {
                $this->managerData['downloadurl'] = $url;
            } elseif (isset($this->managerData['downloadurl'])) {
                unset($this->managerData['downloadurl']);
            }
            if (isset($this->managerData['installed'])) {
                $this->managerData['updated'] = date('r');
            } else {
                $this->managerData['installed'] = date('r');
            }
            $this->writeManagerData();
        }

        if ($this->getID() != $origID) $this->setExtension($origID);
    }

    /**
     * Read the manager.dat file
     */
    protected function readManagerData()
    {
        $managerpath = $this->getInstallDir() . '/manager.dat';
        if (is_readable($managerpath)) {
            $file = @file($managerpath);
            if (!empty($file)) {
                foreach ($file as $line) {
                    [$key, $value] = sexplode('=', trim($line, DOKU_LF), 2, '');
                    $key = trim($key);
                    $value = trim($value);
                    // backwards compatible with old plugin manager
                    if ($key == 'url') $key = 'downloadurl';
                    $this->managerData[$key] = $value;
                }
            }
        }
    }

    /**
     * Write the manager.data file
     */
    protected function writeManagerData()
    {
        $managerpath = $this->getInstallDir() . '/manager.dat';
        $data = '';
        foreach ($this->managerData as $k => $v) {
            $data .= $k . '=' . $v . DOKU_LF;
        }
        io_saveFile($managerpath, $data);
    }

    /**
     * Returns a temporary directory
     *
     * The directory is registered for cleanup when the class is destroyed
     *
     * @return false|string
     */
    protected function mkTmpDir()
    {
        $dir = io_mktmpdir();
        if (!$dir) return false;
        $this->temporary[] = $dir;
        return $dir;
    }

    /**
     * downloads a file from the net and saves it
     *
     * - $file is the directory where the file should be saved
     * - if successful will return the name used for the saved file, false otherwise
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Chris Smith <chris@jalakai.co.uk>
     *
     * @param string $url           url to download
     * @param string $file          path to file or directory where to save
     * @param string $defaultName   fallback for name of download
     * @return bool|string          if failed false, otherwise true or the name of the file in the given dir
     */
    protected function downloadToFile($url, $file, $defaultName = '')
    {
        global $conf;
        $http = new DokuHTTPClient();
        $http->max_bodysize = 0;
        $http->timeout = 25; //max. 25 sec
        $http->keep_alive = false; // we do single ops here, no need for keep-alive
        $http->agent = 'DokuWiki HTTP Client (Extension Manager)';

        $data = $http->get($url);
        if ($data === false) return false;

        $name = '';
        if (isset($http->resp_headers['content-disposition'])) {
            $content_disposition = $http->resp_headers['content-disposition'];
            $match = [];
            if (
                is_string($content_disposition) &&
                preg_match('/attachment;\s*filename\s*=\s*"([^"]*)"/i', $content_disposition, $match)
            ) {
                $name = PhpString::basename($match[1]);
            }
        }

        if (!$name) {
            if (!$defaultName) return false;
            $name = $defaultName;
        }

        $file .= $name;

        $fileexists = file_exists($file);
        $fp = @fopen($file, "w");
        if (!$fp) return false;
        fwrite($fp, $data);
        fclose($fp);
        if (!$fileexists && $conf['fperm']) chmod($file, $conf['fperm']);
        return $name;
    }

    /**
     * Download an archive to a protected path
     *
     * @param string $url  The url to get the archive from
     * @throws Exception   when something goes wrong
     * @return string The path where the archive was saved
     */
    public function download($url)
    {
        // check the url
        if (!preg_match('/https?:\/\//i', $url)) {
            throw new Exception($this->getLang('error_badurl'));
        }

        // try to get the file from the path (used as plugin name fallback)
        $file = parse_url($url, PHP_URL_PATH);
        if (is_null($file)) {
            $file = md5($url);
        } else {
            $file = PhpString::basename($file);
        }

        // create tmp directory for download
        if (!($tmp = $this->mkTmpDir())) {
            throw new Exception($this->getLang('error_dircreate'));
        }

        // download
        if (!$file = $this->downloadToFile($url, $tmp . '/', $file)) {
            io_rmdir($tmp, true);
            throw new Exception(sprintf(
                $this->getLang('error_download'),
                '<bdi>' . hsc($url) . '</bdi>'
            ));
        }

        return $tmp . '/' . $file;
    }

    /**
     * @param string $file      The path to the archive that shall be installed
     * @param bool   $overwrite If an already installed plugin should be overwritten
     * @param string $base      The basename of the plugin if it's known
     * @throws Exception        when something went wrong
     * @return array            list of installed extensions
     */
    public function installArchive($file, $overwrite = false, $base = '')
    {
        $installed_extensions = [];

        // create tmp directory for decompression
        if (!($tmp = $this->mkTmpDir())) {
            throw new Exception($this->getLang('error_dircreate'));
        }

        // add default base folder if specified to handle case where zip doesn't contain this
        if ($base && !@mkdir($tmp . '/' . $base)) {
            throw new Exception($this->getLang('error_dircreate'));
        }

        // decompress
        $this->decompress($file, "$tmp/" . $base);

        // search $tmp/$base for the folder(s) that has been created
        // move the folder(s) to lib/..
        $result = ['old' => [], 'new' => []];
        $default = ($this->isTemplate() ? 'template' : 'plugin');
        if (!$this->findFolders($result, $tmp . '/' . $base, $default)) {
            throw new Exception($this->getLang('error_findfolder'));
        }

        // choose correct result array
        if (count($result['new'])) {
            $install = $result['new'];
        } else {
            $install = $result['old'];
        }

        if (!count($install)) {
            throw new Exception($this->getLang('error_findfolder'));
        }

        // now install all found items
        foreach ($install as $item) {
            // where to install?
            if ($item['type'] == 'template') {
                $target_base_dir = $this->tpllib;
            } else {
                $target_base_dir = DOKU_PLUGIN;
            }

            if (!empty($item['base'])) {
                // use base set in info.txt
            } elseif ($base && count($install) == 1) {
                $item['base'] = $base;
            } else {
                // default - use directory as found in zip
                // plugins from github/master without *.info.txt will install in wrong folder
                // but using $info->id will make 'code3' fail (which should install in lib/code/..)
                $item['base'] = basename($item['tmp']);
            }

            // check to make sure we aren't overwriting anything
            $target = $target_base_dir . $item['base'];
            if (!$overwrite && file_exists($target)) {
                // this info message is not being exposed via exception,
                // so that it's not interrupting the installation
                msg(sprintf($this->getLang('msg_nooverwrite'), $item['base']));
                continue;
            }

            $action = file_exists($target) ? 'update' : 'install';

            // copy action
            if ($this->dircopy($item['tmp'], $target)) {
                // return info
                $id = $item['base'];
                if ($item['type'] == 'template') {
                    $id = 'template:' . $id;
                }
                $installed_extensions[$id] = [
                    'base' => $item['base'],
                    'type' => $item['type'],
                    'action' => $action
                ];
            } else {
                throw new Exception(sprintf(
                    $this->getLang('error_copy') . DOKU_LF,
                    '<bdi>' . $item['base'] . '</bdi>'
                ));
            }
        }

        // cleanup
        if ($tmp) io_rmdir($tmp, true);
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return $installed_extensions;
    }

    /**
     * Find out what was in the extracted directory
     *
     * Correct folders are searched recursively using the "*.info.txt" configs
     * as indicator for a root folder. When such a file is found, it's base
     * setting is used (when set). All folders found by this method are stored
     * in the 'new' key of the $result array.
     *
     * For backwards compatibility all found top level folders are stored as
     * in the 'old' key of the $result array.
     *
     * When no items are found in 'new' the copy mechanism should fall back
     * the 'old' list.
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @param array $result - results are stored here
     * @param string $directory - the temp directory where the package was unpacked to
     * @param string $default_type - type used if no info.txt available
     * @param string $subdir - a subdirectory. do not set. used by recursion
     * @return bool - false on error
     */
    protected function findFolders(&$result, $directory, $default_type = 'plugin', $subdir = '')
    {
        $this_dir = "$directory$subdir";
        $dh       = @opendir($this_dir);
        if (!$dh) return false;

        $found_dirs           = [];
        $found_files          = 0;
        $found_template_parts = 0;
        while (false !== ($f = readdir($dh))) {
            if ($f == '.' || $f == '..') continue;

            if (is_dir("$this_dir/$f")) {
                $found_dirs[] = "$subdir/$f";
            } else {
                // it's a file -> check for config
                $found_files++;
                switch ($f) {
                    case 'plugin.info.txt':
                    case 'template.info.txt':
                        // we have  found a clear marker, save and return
                        $info = [];
                        $type = explode('.', $f, 2);
                        $info['type'] = $type[0];
                        $info['tmp']  = $this_dir;
                        $conf = confToHash("$this_dir/$f");
                        $info['base'] = basename($conf['base']);
                        $result['new'][] = $info;
                        return true;

                    case 'main.php':
                    case 'details.php':
                    case 'mediamanager.php':
                    case 'style.ini':
                        $found_template_parts++;
                        break;
                }
            }
        }
        closedir($dh);

        // files where found but no info.txt - use old method
        if ($found_files) {
            $info        = [];
            $info['tmp'] = $this_dir;
            // does this look like a template or should we use the default type?
            if ($found_template_parts >= 2) {
                $info['type'] = 'template';
            } else {
                $info['type'] = $default_type;
            }

            $result['old'][] = $info;
            return true;
        }

        // we have no files yet -> recurse
        foreach ($found_dirs as $found_dir) {
            $this->findFolders($result, $directory, $default_type, "$found_dir");
        }
        return true;
    }

    /**
     * Decompress a given file to the given target directory
     *
     * Determines the compression type from the file extension
     *
     * @param string $file   archive to extract
     * @param string $target directory to extract to
     * @throws Exception
     * @return bool
     */
    private function decompress($file, $target)
    {
        // decompression library doesn't like target folders ending in "/"
        if (str_ends_with($target, '/')) $target = substr($target, 0, -1);

        $ext = $this->guessArchiveType($file);
        if (in_array($ext, ['tar', 'bz', 'gz'])) {
            try {
                $tar = new Tar();
                $tar->open($file);
                $tar->extract($target);
            } catch (ArchiveIOException $e) {
                throw new Exception($this->getLang('error_decompress') . ' ' . $e->getMessage(), $e->getCode(), $e);
            }

            return true;
        } elseif ($ext == 'zip') {
            try {
                $zip = new Zip();
                $zip->open($file);
                $zip->extract($target);
            } catch (ArchiveIOException $e) {
                throw new Exception($this->getLang('error_decompress') . ' ' . $e->getMessage(), $e->getCode(), $e);
            }

            return true;
        }

        // the only case when we don't get one of the recognized archive types is
        // when the archive file can't be read
        throw new Exception($this->getLang('error_decompress') . ' Couldn\'t read archive file');
    }

    /**
     * Determine the archive type of the given file
     *
     * Reads the first magic bytes of the given file for content type guessing,
     * if neither bz, gz or zip are recognized, tar is assumed.
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @param string $file The file to analyze
     * @return string|false false if the file can't be read, otherwise an "extension"
     */
    private function guessArchiveType($file)
    {
        $fh = fopen($file, 'rb');
        if (!$fh) return false;
        $magic = fread($fh, 5);
        fclose($fh);

        if (strpos($magic, "\x42\x5a") === 0) return 'bz';
        if (strpos($magic, "\x1f\x8b") === 0) return 'gz';
        if (strpos($magic, "\x50\x4b\x03\x04") === 0) return 'zip';
        return 'tar';
    }

    /**
     * Copy with recursive sub-directory support
     *
     * @param string $src filename path to file
     * @param string $dst filename path to file
     * @return bool|int|string
     */
    private function dircopy($src, $dst)
    {
        global $conf;

        if (is_dir($src)) {
            if (!$dh = @opendir($src)) return false;

            if ($ok = io_mkdir_p($dst)) {
                while ($ok && (false !== ($f = readdir($dh)))) {
                    if ($f == '..' || $f == '.') continue;
                    $ok = $this->dircopy("$src/$f", "$dst/$f");
                }
            }

            closedir($dh);
            return $ok;
        } else {
            $existed = file_exists($dst);

            if (!@copy($src, $dst)) return false;
            if (!$existed && $conf['fperm']) chmod($dst, $conf['fperm']);
            @touch($dst, filemtime($src));
        }

        return true;
    }

    /**
     * Delete outdated files from updated plugins
     *
     * @param array $installed
     */
    private function removeDeletedfiles($installed)
    {
        foreach ($installed as $extension) {
            // only on update
            if ($extension['action'] == 'install') continue;

            // get definition file
            if ($extension['type'] == 'template') {
                $extensiondir = $this->tpllib;
            } else {
                $extensiondir = DOKU_PLUGIN;
            }
            $extensiondir = $extensiondir . $extension['base'] . '/';
            $definitionfile = $extensiondir . 'deleted.files';
            if (!file_exists($definitionfile)) continue;

            // delete the old files
            $list = file($definitionfile);

            foreach ($list as $line) {
                $line = trim(preg_replace('/#.*$/', '', $line));
                if (!$line) continue;
                $file = $extensiondir . $line;
                if (!file_exists($file)) continue;

                io_rmdir($file, true);
            }
        }
    }
}

// vim:ts=4:sw=4:et:
