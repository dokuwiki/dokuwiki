<?php

namespace dokuwiki\plugin\extension;

use dokuwiki\Extension\PluginController;
use dokuwiki\HTTP\DokuHTTPClient;
use dokuwiki\Utf8\PhpString;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use splitbrain\PHPArchive\ArchiveCorruptedException;
use splitbrain\PHPArchive\ArchiveIllegalCompressionException;
use splitbrain\PHPArchive\ArchiveIOException;
use splitbrain\PHPArchive\Tar;
use splitbrain\PHPArchive\Zip;

/**
 * Install and deinstall extensions
 *
 * This manages all the file operations and downloads needed to install an extension.
 */
class Installer
{
    /** @var string[] a list of temporary directories used during this installation */
    protected array $temporary = [];

    /** @var bool if changes have been made that require a cache purge */
    protected $isDirty = false;

    /** @var bool Replace existing files? */
    protected $overwrite = false;

    /** @var string The last used URL to install an extension */
    protected $sourceUrl = '';

    protected $processed = [];

    public const STATUS_SKIPPED = 'skipped';
    public const STATUS_UPDATED = 'updated';
    public const STATUS_INSTALLED = 'installed';
    public const STATUS_REMOVED = 'removed';


    /**
     * Initialize a new extension installer
     *
     * @param bool $overwrite
     */
    public function __construct($overwrite = false)
    {
        $this->overwrite = $overwrite;
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
        $this->cleanUp();
    }

    /**
     * Install an extension by ID
     *
     * This will simply call installExtension after constructing an extension from the ID
     *
     * The $skipInstalled parameter should only be used when installing dependencies
     *
     * @param string $id the extension ID
     * @param bool $skipInstalled Ignore the overwrite setting and skip installed extensions
     * @throws Exception
     */
    public function installFromId($id, $skipInstalled = false)
    {
        $extension = Extension::createFromId($id);
        if ($skipInstalled && $extension->isInstalled()) return;
        $this->installExtension($extension);
    }

    /**
     * Install an extension
     *
     * This will simply call installFromUrl() with the URL from the extension
     *
     * @param Extension $extension
     * @throws Exception
     */
    public function installExtension(Extension $extension)
    {
        $url = $extension->getDownloadURL();
        if (!$url) {
            throw new Exception('error_nourl', [$extension->getId()]);
        }
        $this->installFromUrl($url);
    }

    /**
     * Install extensions from a given URL
     *
     * @param string $url the URL to the archive
     * @param null $base the base directory name to use
     * @throws Exception
     */
    public function installFromUrl($url, $base = null)
    {
        $this->sourceUrl = $url;
        $archive = $this->downloadArchive($url);
        $this->installFromArchive(
            $archive,
            $base
        );
    }

    /**
     * Install extensions from a user upload
     *
     * @param string $field name of the upload file
     * @throws Exception
     */
    public function installFromUpload($field)
    {
        $this->sourceUrl = '';
        if ($_FILES[$field]['error']) {
            throw new Exception('msg_upload_failed', [$_FILES[$field]['error']]);
        }

        $tmp = $this->mkTmpDir();
        if (!move_uploaded_file($_FILES[$field]['tmp_name'], "$tmp/upload.archive")) {
            throw new Exception('msg_upload_failed', ['move failed']);
        }
        $this->installFromArchive(
            "$tmp/upload.archive",
            $this->fileToBase($_FILES[$field]['name']),
        );
    }

    /**
     * Install extensions from an archive
     *
     * The archive is extracted to a temporary directory and then the contained extensions are installed.
     * This is is the ultimate installation procedure and all other install methods will end up here.
     *
     * @param string $archive the path to the archive
     * @param string $base the base directory name to use
     * @throws Exception
     */
    public function installFromArchive($archive, $base = null)
    {
        if ($base === null) $base = $this->fileToBase($archive);
        $target = $this->mkTmpDir() . '/' . $base;
        $this->extractArchive($archive, $target);
        $extensions = $this->findExtensions($target, $base);
        foreach ($extensions as $extension) {
            // check installation status
            if ($extension->isInstalled()) {
                if (!$this->overwrite) {
                    $this->processed[$extension->getId()] = self::STATUS_SKIPPED;
                    continue;
                }
                $status = self::STATUS_UPDATED;
            } else {
                $status = self::STATUS_INSTALLED;
            }

            // check PHP requirements
            self::ensurePhpCompatibility($extension);

            // install dependencies first
            foreach ($extension->getDependencyList() as $id) {
                if (isset($this->processed[$id])) continue;
                if ($id == $extension->getId()) continue; // avoid circular dependencies
                $this->installFromId($id, true);
            }

            // now install the extension
            self::ensurePermissions($extension);
            $this->dircopy(
                $extension->getCurrentDir(),
                $extension->getInstallDir()
            );
            $this->isDirty = true;
            $extension->getManager()->storeUpdate($this->sourceUrl);
            $this->removeDeletedFiles($extension);
            $this->processed[$extension->getId()] = $status;
        }

        $this->cleanUp();
    }

    /**
     * Uninstall an extension
     *
     * @param Extension $extension
     * @throws Exception
     */
    public function uninstall(Extension $extension)
    {
        if (!$extension->isInstalled()) {
            throw new Exception('error_notinstalled', [$extension->getId()]);
        }

        if ($extension->isProtected()) {
            throw new Exception('error_uninstall_protected', [$extension->getId()]);
        }

        self::ensurePermissions($extension);

        $dependants = $extension->getDependants();
        if ($dependants !== []) {
            throw new Exception('error_uninstall_dependants', [$extension->getId(), implode(', ', $dependants)]);
        }

        if (!io_rmdir($extension->getInstallDir(), true)) {
            throw new Exception('msg_delete_failed', [$extension->getId()]);
        }
        self::purgeCache();

        $this->processed[$extension->getId()] = self::STATUS_REMOVED;
    }

    /**
     * Enable the extension
     *
     * @throws Exception
     */
    public function enable(Extension $extension)
    {
        if ($extension->isTemplate()) throw new Exception('notimplemented');
        if (!$extension->isInstalled()) throw new Exception('error_notinstalled', [$extension->getId()]);
        if ($extension->isEnabled()) throw new Exception('error_alreadyenabled', [$extension->getId()]);

        /* @var PluginController $plugin_controller */
        global $plugin_controller;
        if (!$plugin_controller->enable($extension->getBase())) {
            throw new Exception('pluginlistsaveerror');
        }
        self::purgeCache();
    }

    /**
     * Disable the extension
     *
     * @throws Exception
     */
    public function disable(Extension $extension)
    {
        if ($extension->isTemplate()) throw new Exception('notimplemented');
        if (!$extension->isInstalled()) throw new Exception('error_notinstalled', [$extension->getId()]);
        if (!$extension->isEnabled()) throw new Exception('error_alreadydisabled', [$extension->getId()]);
        if ($extension->isProtected()) throw new Exception('error_disable_protected', [$extension->getId()]);

        $dependants = $extension->getDependants();
        if ($dependants !== []) {
            throw new Exception('error_disable_dependants', [$extension->getId(), implode(', ', $dependants)]);
        }

        /* @var PluginController $plugin_controller */
        global $plugin_controller;
        if (!$plugin_controller->disable($extension->getBase())) {
            throw new Exception('pluginlistsaveerror');
        }
        self::purgeCache();
    }


    /**
     * Download an archive to a protected path
     *
     * @param string $url The url to get the archive from
     * @return string The path where the archive was saved
     * @throws Exception
     */
    public function downloadArchive($url)
    {
        // check the url
        if (!preg_match('/https?:\/\//i', $url)) {
            throw new Exception('error_badurl');
        }

        // try to get the file from the path (used as plugin name fallback)
        $file = parse_url($url, PHP_URL_PATH);
        $file = $file ? PhpString::basename($file) : md5($url);

        // download
        $http = new DokuHTTPClient();
        $http->max_bodysize = 0;
        $http->keep_alive = false; // we do single ops here, no need for keep-alive
        $http->agent = 'DokuWiki HTTP Client (Extension Manager)';

        // large downloads may take a while on slow connections, so we try to extend the timeout to 4 minutes
        // 4 minutes was chosen, because HTTP servers and proxies often have a 5 minute timeout
        if (PHP_SAPI === 'cli' || @set_time_limit(60 * 4)) {
            $http->timeout = 60 * 4 - 5; // nearly 4 minutes
        } else {
            $http->timeout = 25; // max. 25 sec (a bit less than default execution time)
        }

        $data = $http->get($url);
        if ($data === false) throw new Exception('error_download', [$url, $http->error, $http->status]);

        // get filename from headers
        if (
            preg_match(
                '/attachment;\s*filename\s*=\s*"([^"]*)"/i',
                (string)($http->resp_headers['content-disposition'] ?? ''),
                $match
            )
        ) {
            $file = PhpString::basename($match[1]);
        }

        // clean up filename
        $file = $this->fileToBase($file);

        // create tmp directory for download
        $tmp = $this->mkTmpDir();

        // save the file
        if (@file_put_contents("$tmp/$file", $data) === false) {
            throw new Exception('error_save');
        }

        return "$tmp/$file";
    }


    /**
     * Delete outdated files
     */
    public function removeDeletedFiles(Extension $extension)
    {
        $extensiondir = $extension->getInstallDir();
        $definitionfile = $extensiondir . '/deleted.files';
        if (!file_exists($definitionfile)) return;

        $list = file($definitionfile);
        foreach ($list as $line) {
            $line = trim(preg_replace('/#.*$/', '', $line));
            $line = str_replace('..', '', $line); // do not run out of the extension directory
            if (!$line) continue;

            $file = $extensiondir . '/' . $line;
            if (!file_exists($file)) continue;

            io_rmdir($file, true);
        }
    }

    /**
     * Purge all caches
     */
    public static function purgeCache()
    {
        // expire dokuwiki caches
        // touching local.php expires wiki page, JS and CSS caches
        global $config_cascade;
        @touch(reset($config_cascade['main']['local']));

        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }
    }

    /**
     * Get the list of processed extensions and their status during an installation run
     *
     * @return array id => status
     */
    public function getProcessed()
    {
        return $this->processed;
    }


    /**
     * Ensure that the given extension is compatible with the current PHP version
     *
     * Throws an exception if the extension is not compatible
     *
     * @param Extension $extension
     * @throws Exception
     */
    public static function ensurePhpCompatibility(Extension $extension)
    {
        $min = $extension->getMinimumPHPVersion();
        if ($min && version_compare(PHP_VERSION, $min, '<')) {
            throw new Exception('error_minphp', [$extension->getId(), $min, PHP_VERSION]);
        }

        $max = $extension->getMaximumPHPVersion();
        if ($max && version_compare(PHP_VERSION, $max, '>')) {
            throw new Exception('error_maxphp', [$extension->getId(), $max, PHP_VERSION]);
        }
    }

    /**
     * Ensure the file permissions are correct before attempting to install
     *
     * @throws Exception if the permissions are not correct
     */
    public static function ensurePermissions(Extension $extension)
    {
        $target = $extension->getInstallDir();

        // bundled plugins do not need to be writable
        if ($extension->isBundled()) {
            return;
        }

        // updates
        if (file_exists($target)) {
            if (!is_writable($target)) throw new Exception('noperms');
            return;
        }

        // new installs
        $target = dirname($target);
        if (!is_writable($target)) {
            if ($extension->isTemplate()) throw new Exception('notplperms');
            throw new Exception('nopluginperms');
        }
    }

    /**
     * Get a base name from an archive name (we don't trust)
     *
     * @param string $file
     * @return string
     */
    protected function fileToBase($file)
    {
        $base = PhpString::basename($file);
        $base = preg_replace('/\.(tar\.gz|tar\.bz|tar\.bz2|tar|tgz|tbz|zip)$/', '', $base);
        return preg_replace('/\W+/', '', $base);
    }

    /**
     * Returns a temporary directory
     *
     * The directory is registered for cleanup when the class is destroyed
     *
     * @return string
     * @throws Exception
     */
    protected function mkTmpDir()
    {
        try {
            $dir = io_mktmpdir();
        } catch (\Exception $e) {
            throw new Exception('error_dircreate', [], $e);
        }
        if (!$dir) throw new Exception('error_dircreate');
        $this->temporary[] = $dir;
        return $dir;
    }

    /**
     * Find all extensions in a given directory
     *
     * This allows us to install extensions from archives that contain multiple extensions and
     * also caters for the fact that archives may or may not contain subdirectories for the extension(s).
     *
     * @param string $dir
     * @return Extension[]
     */
    protected function findExtensions($dir, $base = null)
    {
        // first check for plugin.info.txt or template.info.txt
        $extensions = [];
        $iterator = new RecursiveDirectoryIterator($dir);
        foreach (new RecursiveIteratorIterator($iterator) as $file) {
            if (
                $file->getFilename() === 'plugin.info.txt' ||
                $file->getFilename() === 'template.info.txt'
            ) {
                $extensions[] = Extension::createFromDirectory($file->getPath());
            }
        }
        if ($extensions) return $extensions;

        // still nothing? we assume this to be a single extension that is either
        // directly in the given directory or in single subdirectory
        $files = glob($dir . '/*');
        if (count($files) === 1 && is_dir($files[0])) {
            $dir = $files[0];
        }
        $base ??= PhpString::basename($dir);
        return [Extension::createFromDirectory($dir, null, $base)];
    }

    /**
     * Extract the given archive to the given target directory
     *
     * Auto-guesses the archive type
     * @throws Exception
     */
    protected function extractArchive($archive, $target)
    {
        $fh = fopen($archive, 'rb');
        if (!$fh) throw new Exception('error_archive_read', [$archive]);
        $magic = fread($fh, 5);
        fclose($fh);

        if (strpos($magic, "\x50\x4b\x03\x04") === 0) {
            $archiver = new Zip();
        } else {
            $archiver = new Tar();
        }
        try {
            $archiver->open($archive);
            $archiver->extract($target);
        } catch (ArchiveIOException | ArchiveCorruptedException | ArchiveIllegalCompressionException $e) {
            throw new Exception('error_archive_extract', [$archive, $e->getMessage()], $e);
        }
    }

    /**
     * Copy with recursive sub-directory support
     *
     * @param string $src filename path to file
     * @param string $dst filename path to file
     * @throws Exception
     */
    protected function dircopy($src, $dst)
    {
        global $conf;

        if (is_dir($src)) {
            if (!$dh = @opendir($src)) {
                throw new Exception('error_copy_read', [$src]);
            }

            if (io_mkdir_p($dst)) {
                while (false !== ($f = readdir($dh))) {
                    if ($f == '..' || $f == '.') continue;
                    $this->dircopy("$src/$f", "$dst/$f");
                }
            } else {
                throw new Exception('error_copy_mkdir', [$dst]);
            }

            closedir($dh);
        } else {
            $existed = file_exists($dst);

            if (!@copy($src, $dst)) {
                throw new Exception('error_copy_copy', [$src, $dst]);
            }
            if (!$existed && $conf['fperm']) chmod($dst, $conf['fperm']);
            @touch($dst, filemtime($src));
        }
    }

    /**
     * Reset caches if needed
     */
    protected function cleanUp()
    {
        if ($this->isDirty) {
            self::purgeCache();
            $this->isDirty = false;
        }
    }
}
