<?php

use dokuwiki\Extension\RemotePlugin;
use dokuwiki\plugin\extension\Extension;
use dokuwiki\plugin\extension\ExtensionApiResponse;
use dokuwiki\plugin\extension\Installer;
use dokuwiki\plugin\extension\Local;
use dokuwiki\plugin\extension\Repository;
use dokuwiki\Remote\AccessDeniedException;

/**
 * DokuWiki Plugin extension (Remote Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author Andreas Gohr <andi@splitbrain.org>
 */
class remote_plugin_extension extends RemotePlugin
{
    /**
     * List installed extensions
     *
     * This lists all installed extensions. The list is not sorted in any way.
     *
     * @return ExtensionApiResponse[] The list of installed extensions and their details
     */
    public function list()
    {
        if (!auth_isadmin()) {
            throw new AccessDeniedException('Only admins are allowed to access extensions', 114);
        }

        $extensions = (new Local())->getExtensions();
        Repository::getInstance()->initExtensions(array_keys($extensions));

        return array_values(
            array_map(
                static fn($extension) => new ExtensionApiResponse($extension),
                $extensions
            )
        );
    }

    /**
     * Search for extensions in the repository
     *
     * @param string $query The keyword(s) to search for
     * @param int $max Maximum number of results (default 10)
     * @return ExtensionApiResponse[] List of matching extensions
     */
    public function search($query, $max = 10)
    {
        if (!auth_isadmin()) {
            throw new AccessDeniedException('Only admins are allowed to access extensions', 114);
        }

        $repo = Repository::getInstance();
        $result = $repo->searchExtensions($query);

        if ($max > 0) {
            $result = array_slice($result, 0, $max);
        }

        return array_values(
            array_map(
                static fn($extension) => new ExtensionApiResponse($extension),
                $result
            )
        );
    }

    /**
     * Enable a specific extension
     *
     * @param string $extension Extension ID to enable
     * @return bool Success status
     */
    public function enable($extension)
    {
        if (!auth_isadmin()) {
            throw new AccessDeniedException('Only admins are allowed to manage extensions', 114);
        }

        $ext = Extension::createFromId($extension);
        $ext->enable();
        return true;
    }

    /**
     * Disable a specific extension
     *
     * @param string $extension Extension ID to disable
     * @return bool Success status
     */
    public function disable($extension)
    {
        if (!auth_isadmin()) {
            throw new AccessDeniedException('Only admins are allowed to manage extensions', 114);
        }

        $ext = Extension::createFromId($extension);
        $ext->disable();
        return true;
    }

    /**
     * Install a specific extension
     *
     * This will also install dependencies, so more than the given extension may be installed.
     *
     * @param string $extension Extension ID or download URL
     * @return string[] List of installed extensions
     */
    public function install($extension)
    {
        if (!auth_isadmin()) {
            throw new AccessDeniedException('Only admins are allowed to manage extensions', 114);
        }

        $installer = new Installer(true);
        $installer->installFromId($extension);

        return array_keys(
            array_filter(
                $installer->getProcessed(),
                static fn($status) => (
                    $status == Installer::STATUS_INSTALLED || $status == Installer::STATUS_UPDATED
                )
            )
        );
    }

    /**
     * Uninstall a specific extension
     *
     * @param string $extension Extension ID to uninstall
     * @return bool Success status
     */
    public function uninstall($extension)
    {
        if (!auth_isadmin()) {
            throw new AccessDeniedException('Only admins are allowed to manage extensions', 114);
        }

        $ext = Extension::createFromId($extension);
        $installer = new Installer();
        $installer->uninstall($ext);
        return true;
    }
}
