<?php

/**
 * Load all internal libraries and setup class autoloader
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */

namespace dokuwiki;

use dokuwiki\Extension\PluginController;

return new class {
    /** @var string[] Common libraries that are always loaded */
    protected array $commonLibs = [
        'defines.php',
        'actions.php',
        'changelog.php',
        'common.php',
        'confutils.php',
        'pluginutils.php',
        'form.php',
        'fulltext.php',
        'html.php',
        'httputils.php',
        'indexer.php',
        'infoutils.php',
        'io.php',
        'mail.php',
        'media.php',
        'pageutils.php',
        'parserutils.php',
        'search.php',
        'template.php',
        'toolbar.php',
        'utf8.php',
        'auth.php',
        'compatibility.php',
        'deprecated.php',
        'legacy.php',
    ];

    /** @var string[] Classname to file mappings */
    protected array $fixedClassNames = [
        'Diff' => 'DifferenceEngine.php',
        'UnifiedDiffFormatter' => 'DifferenceEngine.php',
        'TableDiffFormatter' => 'DifferenceEngine.php',
        'cache' => 'cache.php',
        'cache_parser' => 'cache.php',
        'cache_instructions' => 'cache.php',
        'cache_renderer' => 'cache.php',
        'JpegMeta' => 'JpegMeta.php',
        'FeedParser' => 'FeedParser.php',
        'SafeFN' => 'SafeFN.class.php',
        'Mailer' => 'Mailer.class.php',
        'Doku_Handler' => 'parser/handler.php',
        'Doku_Renderer' => 'parser/renderer.php',
        'Doku_Renderer_xhtml' => 'parser/xhtml.php',
        'Doku_Renderer_code' => 'parser/code.php',
        'Doku_Renderer_xhtmlsummary' => 'parser/xhtmlsummary.php',
        'Doku_Renderer_metadata' => 'parser/metadata.php'
    ];

    /**
     * Load common libs and register autoloader
     */
    public function __construct()
    {
        require_once(DOKU_INC . 'vendor/autoload.php');
        spl_autoload_register([$this, 'autoload']);
        $this->loadCommonLibs();
    }

    /**
     * require all the common libraries
     *
     * @return true
     */
    public function loadCommonLibs()
    {
        foreach ($this->commonLibs as $lib) {
            require_once(DOKU_INC . 'inc/' . $lib);
        }
        return true;
    }

    /**
     * spl_autoload_register callback
     *
     * @param string $className
     * @return bool
     */
    public function autoload($className)
    {
        // namespace to directory conversion
        $classPath = str_replace('\\', '/', $className);

        return $this->autoloadFixedClass($className)
            || $this->autoloadTestMockClass($classPath)
            || $this->autoloadTestClass($classPath)
            || $this->autoloadPluginClass($classPath)
            || $this->autoloadTemplateClass($classPath)
            || $this->autoloadCoreClass($classPath)
            || $this->autoloadNamedPluginClass($className);
    }

    /**
     * Check if the class is one of the fixed names
     *
     * @param string $className
     * @return bool true if the class was loaded, false otherwise
     */
    protected function autoloadFixedClass($className)
    {
        if (isset($this->fixedClassNames[$className])) {
            require($this->fixedClassNames[$className]);
            return true;
        }
        return false;
    }

    /**
     * Check if the class is a test mock class
     *
     * @param string $classPath The class name using forward slashes as namespace separators
     * @return bool true if the class was loaded, false otherwise
     */
    protected function autoloadTestMockClass($classPath)
    {
        if ($this->prefixStrip($classPath, 'dokuwiki/test/mock/')) {
            $file = DOKU_INC . '_test/mock/' . $classPath . '.php';
            if (file_exists($file)) {
                require $file;
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the class is a test mock class
     *
     * @param string $classPath The class name using forward slashes as namespace separators
     * @return bool true if the class was loaded, false otherwise
     */
    protected function autoloadTestClass($classPath)
    {
        if ($this->prefixStrip($classPath, 'dokuwiki/test/')) {
            $file = DOKU_INC . '_test/tests/' . $classPath . '.php';
            if (file_exists($file)) {
                require $file;
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the class is a namespaced plugin class
     *
     * @param string $classPath The class name using forward slashes as namespace separators
     * @return bool true if the class was loaded, false otherwise
     */
    protected function autoloadPluginClass($classPath)
    {
        global $plugin_controller;

        if ($this->prefixStrip($classPath, 'dokuwiki/plugin/')) {
            $classPath = str_replace('/test/', '/_test/', $classPath); // no underscore in test namespace
            $file = DOKU_PLUGIN . $classPath . '.php';
            if (file_exists($file)) {
                $plugin = substr($classPath, 0, strpos($classPath, '/'));
                // don't load disabled plugin classes (only if plugin controller is available)
                if (!defined('DOKU_UNITTEST') && $plugin_controller && plugin_isdisabled($plugin)) return false;

                try {
                    require $file;
                } catch (\Throwable $e) {
                    ErrorHandler::showExceptionMsg($e, "Error loading plugin $plugin");
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the class is a namespaced template class
     *
     * @param string $classPath The class name using forward slashes as namespace separators
     * @return bool true if the class was loaded, false otherwise
     */
    protected function autoloadTemplateClass($classPath)
    {
        // template namespace
        if ($this->prefixStrip($classPath, 'dokuwiki/template/')) {
            $classPath = str_replace('/test/', '/_test/', $classPath); // no underscore in test namespace
            $file = DOKU_INC . 'lib/tpl/' . $classPath . '.php';
            if (file_exists($file)) {
                $template = substr($classPath, 0, strpos($classPath, '/'));

                try {
                    require $file;
                } catch (\Throwable $e) {
                    ErrorHandler::showExceptionMsg($e, "Error loading template $template");
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the class is a namespaced DokuWiki core class
     *
     * @param string $classPath The class name using forward slashes as namespace separators
     * @return bool true if the class was loaded, false otherwise
     */
    protected function autoloadCoreClass($classPath)
    {
        if ($this->prefixStrip($classPath, 'dokuwiki/')) {
            $file = DOKU_INC . 'inc/' . $classPath . '.php';
            if (file_exists($file)) {
                require $file;
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the class is a un-namespaced plugin class following our naming scheme
     *
     * @param string $className
     * @return bool true if the class was loaded, false otherwise
     */
    protected function autoloadNamedPluginClass($className)
    {
        global $plugin_controller;

        if (
            preg_match(
                '/^(' . implode('|', PluginController::PLUGIN_TYPES) . ')_plugin_(' .
                DOKU_PLUGIN_NAME_REGEX .
                ')(?:_([^_]+))?$/',
                $className,
                $m
            )
        ) {
            $c = ((count($m) === 4) ? "/{$m[3]}" : '');
            $plg = DOKU_PLUGIN . "{$m[2]}/{$m[1]}$c.php";
            if (file_exists($plg)) {
                // don't load disabled plugin classes (only if plugin controller is available)
                if (!defined('DOKU_UNITTEST') && $plugin_controller && plugin_isdisabled($m[2])) return false;
                try {
                    require $plg;
                } catch (\Throwable $e) {
                    ErrorHandler::showExceptionMsg($e, "Error loading plugin {$m[2]}");
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Check if the given string starts with the given prefix and strip it
     *
     * @param string $string
     * @param string $prefix
     * @return bool true if the prefix was found and stripped, false otherwise
     */
    protected function prefixStrip(&$string, $prefix)
    {
        if (str_starts_with($string, $prefix)) {
            $string = substr($string, strlen($prefix));
            return true;
        }
        return false;
    }
};
