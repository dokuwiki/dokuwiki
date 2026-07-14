<?php

namespace dokuwiki\plugin\extension;

use dokuwiki\Logger;
use RuntimeException;

class Local
{
    /**
     * Glob the given directory and init each subdirectory as an Extension
     *
     * Directories that cannot be initialized as an extension (e.g. leftover
     * directories with an invalid base name like "myplugin (copy)") are skipped
     * so a single stray directory does not break the whole listing.
     *
     * @param string $directory
     * @return Extension[]
     */
    protected function readExtensionsFromDirectory($directory)
    {
        $extensions = [];
        $directory = rtrim($directory, '/');
        $dirs = glob($directory . '/*', GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            try {
                $ext = Extension::createFromDirectory($dir);
            } catch (RuntimeException $e) {
                Logger::debug('Skipping invalid extension directory: ' . $dir, $e->getMessage());
                continue;
            }
            $extensions[$ext->getId()] = $ext;
        }
        return $extensions;
    }

    /**
     * Get all locally installed templates
     *
     * @return Extension[]
     */
    public function getTemplates()
    {
        $templates = $this->readExtensionsFromDirectory(DOKU_INC . 'lib/tpl/');
        ksort($templates);
        return $templates;
    }

    /**
     * Get all locally installed plugins
     *
     * Note this skips the PluginController and just iterates over the plugin dir,
     * it's basically the same as what the PluginController does, but allows us to
     * directly initialize Extension objects.
     *
     * @return Extension[]
     */
    public function getPlugins()
    {
        $plugins = $this->readExtensionsFromDirectory(DOKU_PLUGIN);
        ksort($plugins);
        return $plugins;
    }

    /**
     * Get all locally installed extensions
     *
     * @return Extension[]
     */
    public function getExtensions()
    {
        return array_merge($this->getPlugins(), $this->getTemplates());
    }
}
