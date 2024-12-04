<?php

namespace dokuwiki\plugin\extension;

use dokuwiki\Extension\PluginController;
use dokuwiki\Utf8\PhpString;
use RuntimeException;

class Extension
{
    public const TYPE_PLUGIN = 'plugin';
    public const TYPE_TEMPLATE = 'template';

    /** @var string[] The types the API uses for plugin components */
    public const COMPONENT_TYPES = [
        1 => 'Syntax',
        2 => 'Admin',
        4 => 'Action',
        8 => 'Render',
        16 => 'Helper',
        32 => 'Template',
        64 => 'Remote',
        128 => 'Auth',
        256 => 'CLI',
        512 => 'CSS/JS-only',
    ];

    /** @var string "plugin"|"template" */
    protected string $type = self::TYPE_PLUGIN;

    /** @var string The base name of this extension */
    protected string $base;

    /** @var string The current location of this extension */
    protected string $currentDir = '';

    /** @var array The local info array of the extension */
    protected array $localInfo = [];

    /** @var array The remote info array of the extension */
    protected array $remoteInfo = [];

    /** @var Manager|null The manager for this extension */
    protected ?Manager $manager = null;

    // region Constructors

    /**
     * The main constructor is private to force the use of the factory methods
     */
    protected function __construct()
    {
    }

    /**
     * Initializes an extension from an id
     *
     * @param string $id The id of the extension
     * @return Extension
     */
    public static function createFromId($id)
    {
        $extension = new self();
        $extension->initFromId($id);
        return $extension;
    }

    protected function initFromId($id)
    {
        [$type, $base] = $this->idToTypeBase($id);
        $this->type = $type;
        $this->base = $base;
        $this->readLocalInfo();
    }

    /**
     * Initializes an extension from a directory
     *
     * The given directory might be the one where the extension has already been installed to
     * or it might be the extracted source in some temporary directory.
     *
     * @param string $dir Where the extension code is currently located
     * @param string|null $type TYPE_PLUGIN|TYPE_TEMPLATE, null for auto-detection
     * @param string $base The base name of the extension, null for auto-detection
     * @return Extension
     */
    public static function createFromDirectory($dir, $type = null, $base = null)
    {
        $extension = new self();
        $extension->initFromDirectory($dir, $type, $base);
        return $extension;
    }

    protected function initFromDirectory($dir, $type = null, $base = null)
    {
        if (!is_dir($dir)) throw new RuntimeException('Directory not found: ' . $dir);
        $this->currentDir = fullpath($dir);

        if ($type === null || $type === self::TYPE_TEMPLATE) {
            if (
                file_exists($dir . '/template.info.txt') ||
                file_exists($dir . '/style.ini') ||
                file_exists($dir . '/main.php') ||
                file_exists($dir . '/detail.php') ||
                file_exists($dir . '/mediamanager.php')
            ) {
                $this->type = self::TYPE_TEMPLATE;
            }
        } else {
            $this->type = self::TYPE_PLUGIN;
        }

        $this->readLocalInfo();

        if ($base !== null) {
            $this->base = $base;
        } elseif (isset($this->localInfo['base'])) {
            $this->base = $this->localInfo['base'];
        } else {
            $this->base = basename($dir);
        }
    }

    /**
     * Initializes an extension from remote data
     *
     * @param array $data The data as returned by the repository api
     * @return Extension
     */
    public static function createFromRemoteData($data)
    {
        $extension = new self();
        $extension->initFromRemoteData($data);
        return $extension;
    }

    protected function initFromRemoteData($data)
    {
        if (!isset($data['plugin'])) throw new RuntimeException('Invalid remote data');

        [$type, $base] = $this->idToTypeBase($data['plugin']);
        $this->remoteInfo = $data;
        $this->type = $type;
        $this->base = $base;

        if ($this->isInstalled()) {
            $this->currentDir = $this->getInstallDir();
            $this->readLocalInfo();
        }
    }

    // endregion

    // region Getters

    /**
     * @param bool $wrap If true, the id is wrapped in backticks
     * @return string The extension id (same as base but prefixed with "template:" for templates)
     */
    public function getId($wrap = false)
    {
        if ($this->type === self::TYPE_TEMPLATE) {
            $id = self::TYPE_TEMPLATE . ':' . $this->base;
        } else {
            $id = $this->base;
        }
        if ($wrap) $id = "`$id`";
        return $id;
    }

    /**
     * Get the base name of this extension
     *
     * @return string
     */
    public function getBase()
    {
        return $this->base;
    }

    /**
     * Get the type of the extension
     *
     * @return string "plugin"|"template"
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * The current directory of the extension
     *
     * @return string|null
     */
    public function getCurrentDir()
    {
        // recheck that the current currentDir is still valid
        if ($this->currentDir && !is_dir($this->currentDir)) {
            $this->currentDir = '';
        }

        // if the extension is installed, then the currentDir is the install dir!
        if (!$this->currentDir && $this->isInstalled()) {
            $this->currentDir = $this->getInstallDir();
        }

        return $this->currentDir;
    }

    /**
     * Get the directory where this extension should be installed in
     *
     * Note: this does not mean that the extension is actually installed there
     *
     * @return string
     */
    public function getInstallDir()
    {
        if ($this->isTemplate()) {
            $dir = dirname(tpl_incdir()) . '/' . $this->base;
        } else {
            $dir = DOKU_PLUGIN . $this->base;
        }

        return fullpath($dir);
    }


    /**
     * Get the display name of the extension
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->getTag('name', PhpString::ucwords($this->getBase() . ' ' . $this->getType()));
    }

    /**
     * Get the author name of the extension
     *
     * @return string Returns an empty string if the author info is missing
     */
    public function getAuthor()
    {
        return $this->getTag('author');
    }

    /**
     * Get the email of the author of the extension if there is any
     *
     * @return string Returns an empty string if the email info is missing
     */
    public function getEmail()
    {
        // email is only in the local data
        return $this->localInfo['email'] ?? '';
    }

    /**
     * Get the email id, i.e. the md5sum of the email
     *
     * @return string Empty string if no email is available
     */
    public function getEmailID()
    {
        if (!empty($this->remoteInfo['emailid'])) return $this->remoteInfo['emailid'];
        if (!empty($this->localInfo['email'])) return md5($this->localInfo['email']);
        return '';
    }

    /**
     * Get the description of the extension
     *
     * @return string Empty string if no description is available
     */
    public function getDescription()
    {
        return $this->getTag(['desc', 'description']);
    }

    /**
     * Get the URL of the extension, usually a page on dokuwiki.org
     *
     * @return string
     */
    public function getURL()
    {
        return $this->getTag(
            'url',
            'https://www.dokuwiki.org/' .
            ($this->isTemplate() ? 'template' : 'plugin') . ':' . $this->getBase()
        );
    }

    /**
     * Get the version of the extension that is actually installed
     *
     * Returns an empty string if the version is not available
     *
     * @return string
     */
    public function getInstalledVersion()
    {
        return $this->localInfo['date'] ?? '';
    }

    /**
     * Get the types of components this extension provides
     *
     * @return array int -> type
     */
    public function getComponentTypes()
    {
        // for installed extensions we can check the files
        if ($this->isInstalled()) {
            if ($this->isTemplate()) {
                return ['Template'];
            } else {
                $types = [];
                foreach (self::COMPONENT_TYPES as $type) {
                    $check = strtolower($type);
                    if (
                        file_exists($this->getInstallDir() . '/' . $check . '.php') ||
                        is_dir($this->getInstallDir() . '/' . $check)
                    ) {
                        $types[] = $type;
                    }
                }
                return $types;
            }
        }
        // still, here? use the remote info
        return $this->getTag('types', []);
    }

    /**
     * Get a list of extension ids this extension depends on
     *
     * @return string[]
     */
    public function getDependencyList()
    {
        return $this->getTag('depends', []);
    }

    /**
     * Get a list of extensions that are currently installed, enabled and depend on this extension
     *
     * @return Extension[]
     */
    public function getDependants()
    {
        $local = new Local();
        $extensions = $local->getExtensions();
        $dependants = [];
        foreach ($extensions as $extension) {
            if (
                in_array($this->getId(), $extension->getDependencyList()) &&
                $extension->isEnabled()
            ) {
                $dependants[$extension->getId()] = $extension;
            }
        }
        return $dependants;
    }

    /**
     * Return the minimum PHP version required by the extension
     *
     * Empty if not set
     *
     * @return string
     */
    public function getMinimumPHPVersion()
    {
        return $this->getTag('phpmin', '');
    }

    /**
     * Return the minimum PHP version supported by the extension
     *
     * @return string
     */
    public function getMaximumPHPVersion()
    {
        return $this->getTag('phpmax', '');
    }

    /**
     * Is this extension a template?
     *
     * @return bool false if it is a plugin
     */
    public function isTemplate()
    {
        return $this->type === self::TYPE_TEMPLATE;
    }

    /**
     * Is the extension installed locally?
     *
     * @return bool
     */
    public function isInstalled()
    {
        return is_dir($this->getInstallDir());
    }

    /**
     * Is the extension under git control?
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
        $this->loadRemoteInfo();
        return $this->remoteInfo['bundled'] ?? in_array(
            $this->getId(),
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
     * Is the extension protected against any modification (disable/uninstall)
     *
     * @return bool if the extension is protected
     */
    public function isProtected()
    {
        // never allow deinstalling the current auth plugin:
        global $conf;
        if ($this->getId() == $conf['authtype']) return true;

        // disallow current template to be uninstalled
        if ($this->isTemplate() && ($this->getBase() === $conf['template'])) return true;

        /** @var PluginController $plugin_controller */
        global $plugin_controller;
        $cascade = $plugin_controller->getCascade();
        return ($cascade['protected'][$this->getId()] ?? false);
    }

    /**
     * Is the extension installed in the correct directory?
     *
     * @return bool
     */
    public function isInWrongFolder()
    {
        if (!$this->isInstalled()) return false;
        return $this->getInstallDir() != $this->currentDir;
    }

    /**
     * Is the extension enabled?
     *
     * @return bool
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
     * Has the download URL changed since the last download?
     *
     * @return bool
     */
    public function hasChangedURL()
    {
        $last = $this->getManager()->getDownloadURL();
        if (!$last) return false;
        return $last !== $this->getDownloadURL();
    }

    /**
     * Is an update available for this extension?
     *
     * @return bool
     */
    public function isUpdateAvailable()
    {
        if ($this->isBundled()) return false; // bundled extensions are never updated
        $self = $this->getInstalledVersion();
        $remote = $this->getLastUpdate();
        return $self < $remote;
    }

    // endregion

    // region Remote Info

    /**
     * Get the date of the last available update
     *
     * @return string yyyy-mm-dd
     */
    public function getLastUpdate()
    {
        return $this->getRemoteTag('lastupdate');
    }

    /**
     * Get a list of tags this extension is tagged with at dokuwiki.org
     *
     * @return string[]
     */
    public function getTags()
    {
        return $this->getRemoteTag('tags', []);
    }

    /**
     * Get the popularity of the extension
     *
     * This is a float between 0 and 1
     *
     * @return float
     */
    public function getPopularity()
    {
        return (float)$this->getRemoteTag('popularity', 0);
    }

    /**
     * Get the text of the update message if there is any
     *
     * @return string
     */
    public function getUpdateMessage()
    {
        return $this->getRemoteTag('updatemessage');
    }

    /**
     * Get the text of the security warning if there is any
     *
     * @return string
     */
    public function getSecurityWarning()
    {
        return $this->getRemoteTag('securitywarning');
    }

    /**
     * Get the text of the security issue if there is any
     *
     * @return string
     */
    public function getSecurityIssue()
    {
        return $this->getRemoteTag('securityissue');
    }

    /**
     * Get the URL of the screenshot of the extension if there is any
     *
     * @return string
     */
    public function getScreenshotURL()
    {
        return $this->getRemoteTag('screenshoturl');
    }

    /**
     * Get the URL of the thumbnail of the extension if there is any
     *
     * @return string
     */
    public function getThumbnailURL()
    {
        return $this->getRemoteTag('thumbnailurl');
    }

    /**
     * Get the download URL of the extension if there is any
     *
     * @return string
     */
    public function getDownloadURL()
    {
        return $this->getRemoteTag('downloadurl');
    }

    /**
     * Get the bug tracker URL of the extension if there is any
     *
     * @return string
     */
    public function getBugtrackerURL()
    {
        return $this->getRemoteTag('bugtracker');
    }

    /**
     * Get the URL of the source repository if there is any
     *
     * @return string
     */
    public function getSourcerepoURL()
    {
        return $this->getRemoteTag('sourcerepo');
    }

    /**
     * Get the donation URL of the extension if there is any
     *
     * @return string
     */
    public function getDonationURL()
    {
        return $this->getRemoteTag('donationurl');
    }

    /**
     * Get a list of extensions that are similar to this one
     *
     * @return string[]
     */
    public function getSimilarList()
    {
        return $this->getRemoteTag('similar', []);
    }

    /**
     * Get a list of extensions that are marked as conflicting with this one
     *
     * @return string[]
     */
    public function getConflictList()
    {
        return $this->getRemoteTag('conflicts', []);
    }

    /**
     * Get a list of DokuWiki versions this plugin is marked as compatible with
     *
     * @return string[][] date -> version
     */
    public function getCompatibleVersions()
    {
        return $this->getRemoteTag('compatible', []);
    }

    // endregion

    // region Actions

    /**
     * Install or update the extension
     *
     * @throws Exception
     */
    public function installOrUpdate()
    {
        $installer = new Installer(true);
        $installer->installExtension($this);
    }

    /**
     * Uninstall the extension
     * @throws Exception
     */
    public function uninstall()
    {
        $installer = new Installer(true);
        $installer->uninstall($this);
    }

    /**
     * Toggle the extension between enabled and disabled
     * @return void
     * @throws Exception
     */
    public function toggle()
    {
        if ($this->isEnabled()) {
            $this->disable();
        } else {
            $this->enable();
        }
    }

    /**
     * Enable the extension
     *
     * @throws Exception
     */
    public function enable()
    {
        (new Installer())->enable($this);
    }

    /**
     * Disable the extension
     *
     * @throws Exception
     */
    public function disable()
    {
        (new Installer())->disable($this);
    }

    // endregion

    // region Meta Data Management

    /**
     * Access the Manager for this extension
     *
     * @return Manager
     */
    public function getManager()
    {
        if (!$this->manager instanceof Manager) {
            $this->manager = new Manager($this);
        }
        return $this->manager;
    }

    /**
     * Reads the info file of the extension if available and fills the localInfo array
     */
    protected function readLocalInfo()
    {
        if (!$this->getCurrentDir()) return;
        $file = $this->currentDir . '/' . $this->type . '.info.txt';
        if (!is_readable($file)) return;
        $this->localInfo = confToHash($file, true);
        $this->localInfo = array_filter($this->localInfo); // remove all falsy keys
    }

    /**
     * Fetches the remote info from the repository
     *
     * This ignores any errors coming from the repository and just sets the remoteInfo to an empty array in that case
     */
    protected function loadRemoteInfo()
    {
        if ($this->remoteInfo) return;
        $remote = Repository::getInstance();
        try {
            $this->remoteInfo = (array)$remote->getExtensionData($this->getId());
        } catch (Exception $e) {
            $this->remoteInfo = [];
        }
    }

    /**
     * Read information from either local or remote info
     *
     * Always prefers local info over remote info. Giving multiple keys is useful when the
     * key has been renamed in the past or if local and remote keys might differ.
     *
     * @param string|string[] $tag one or multiple keys to check
     * @param mixed $default
     * @return mixed
     */
    protected function getTag($tag, $default = '')
    {
        foreach ((array)$tag as $t) {
            if (isset($this->localInfo[$t])) return $this->localInfo[$t];
        }

        return $this->getRemoteTag($tag, $default);
    }

    /**
     * Read information from remote info
     *
     * @param string|string[] $tag one or mutiple keys to check
     * @param mixed $default
     * @return mixed
     */
    protected function getRemoteTag($tag, $default = '')
    {
        $this->loadRemoteInfo();
        foreach ((array)$tag as $t) {
            if (isset($this->remoteInfo[$t])) return $this->remoteInfo[$t];
        }
        return $default;
    }

    // endregion

    // region utilities

    /**
     * Convert an extension id to a type and base
     *
     * @param string $id
     * @return array [type, base]
     */
    protected function idToTypeBase($id)
    {
        [$type, $base] = sexplode(':', $id, 2);
        if ($base === null) {
            $base = $type;
            $type = self::TYPE_PLUGIN;
        } elseif ($type === self::TYPE_TEMPLATE) {
            $type = self::TYPE_TEMPLATE;
        } else {
            throw new RuntimeException('Invalid extension id: ' . $id);
        }

        return [$type, $base];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getId();
    }

    // endregion
}
