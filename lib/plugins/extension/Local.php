<?php

namespace easywiki\plugin\extension;

class Local
{
    /**
     * Glob the given directory and init each subdirectory as an Extension
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
            $ext = Extension::createFromDirectory($dir);
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
        $templates = $this->readExtensionsFromDirectory(WIKI_INC . 'lib/tpl/');
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
        $plugins = $this->readExtensionsFromDirectory(WIKI_PLUGIN);
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
