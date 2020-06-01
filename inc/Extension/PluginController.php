<?php

namespace dokuwiki\Extension;

/**
 * Class to encapsulate access to dokuwiki plugins
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 */
class PluginController
{
    /** @var array the types of plugins DokuWiki supports */
    const PLUGIN_TYPES = ['auth', 'admin', 'syntax', 'action', 'renderer', 'helper', 'remote', 'cli'];

    protected $listByType = [];
    /** @var array all installed plugins and their enabled state [plugin=>enabled] */
    protected $masterList = [];
    protected $pluginCascade = ['default' => [], 'local' => [], 'protected' => []];
    protected $lastLocalConfigFile = '';

    /**
     * Populates the master list of plugins
     */
    public function __construct()
    {
        $this->loadConfig();
        $this->populateMasterList();
    }

    /**
     * Returns a list of available plugins of given type
     *
     * @param $type  string, plugin_type name;
     *               the type of plugin to return,
     *               use empty string for all types
     * @param $all   bool;
     *               false to only return enabled plugins,
     *               true to return both enabled and disabled plugins
     *
     * @return       array of
     *                  - plugin names when $type = ''
     *                  - or plugin component names when a $type is given
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    public function getList($type = '', $all = false)
    {

        // request the complete list
        if (!$type) {
            return $all ? array_keys($this->masterList) : array_keys(array_filter($this->masterList));
        }

        if (!isset($this->listByType[$type]['enabled'])) {
            $this->listByType[$type]['enabled'] = $this->getListByType($type, true);
        }
        if ($all && !isset($this->listByType[$type]['disabled'])) {
            $this->listByType[$type]['disabled'] = $this->getListByType($type, false);
        }

        return $all
            ? array_merge($this->listByType[$type]['enabled'], $this->listByType[$type]['disabled'])
            : $this->listByType[$type]['enabled'];
    }

    /**
     * Loads the given plugin and creates an object of it
     *
     * @param  $type     string type of plugin to load
     * @param  $name     string name of the plugin to load
     * @param  $new      bool   true to return a new instance of the plugin, false to use an already loaded instance
     * @param  $disabled bool   true to load even disabled plugins
     * @return PluginInterface|null  the plugin object or null on failure
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     */
    public function load($type, $name, $new = false, $disabled = false)
    {

        //we keep all loaded plugins available in global scope for reuse
        global $DOKU_PLUGINS;

        list($plugin, /* $component */) = $this->splitName($name);

        // check if disabled
        if (!$disabled && !$this->isEnabled($plugin)) {
            return null;
        }

        $class = $type . '_plugin_' . $name;

        //plugin already loaded?
        if (!empty($DOKU_PLUGINS[$type][$name])) {
            if ($new || !$DOKU_PLUGINS[$type][$name]->isSingleton()) {
                return class_exists($class, true) ? new $class : null;
            }

            return $DOKU_PLUGINS[$type][$name];
        }

        //construct class and instantiate
        if (!class_exists($class, true)) {

            # the plugin might be in the wrong directory
            $inf = confToHash(DOKU_PLUGIN . "$plugin/plugin.info.txt");
            if ($inf['base'] && $inf['base'] != $plugin) {
                msg(
                    sprintf(
                        "Plugin installed incorrectly. Rename plugin directory '%s' to '%s'.",
                        hsc($plugin),
                        hsc(
                            $inf['base']
                        )
                    ), -1
                );
            } elseif (preg_match('/^' . DOKU_PLUGIN_NAME_REGEX . '$/', $plugin) !== 1) {
                msg(
                    sprintf(
                        "Plugin name '%s' is not a valid plugin name, only the characters a-z and 0-9 are allowed. " .
                        'Maybe the plugin has been installed in the wrong directory?', hsc($plugin)
                    ), -1
                );
            }
            return null;
        }

        $DOKU_PLUGINS[$type][$name] = new $class;
        return $DOKU_PLUGINS[$type][$name];
    }

    /**
     * Whether plugin is disabled
     *
     * @param string $plugin name of plugin
     * @return bool  true disabled, false enabled
     * @deprecated in favor of the more sensible isEnabled where the return value matches the enabled state
     */
    public function isDisabled($plugin)
    {
        dbg_deprecated('isEnabled()');
        return !$this->isEnabled($plugin);
    }

    /**
     * Check whether plugin is disabled
     *
     * @param string $plugin name of plugin
     * @return bool  true enabled, false disabled
     */
    public function isEnabled($plugin)
    {
        return !empty($this->masterList[$plugin]);
    }

    /**
     * Disable the plugin
     *
     * @param string $plugin name of plugin
     * @return bool  true saving succeed, false saving failed
     */
    public function disable($plugin)
    {
        if (array_key_exists($plugin, $this->pluginCascade['protected'])) return false;
        $this->masterList[$plugin] = 0;
        return $this->saveList();
    }

    /**
     * Enable the plugin
     *
     * @param string $plugin name of plugin
     * @return bool  true saving succeed, false saving failed
     */
    public function enable($plugin)
    {
        if (array_key_exists($plugin, $this->pluginCascade['protected'])) return false;
        $this->masterList[$plugin] = 1;
        return $this->saveList();
    }

    /**
     * Returns cascade of the config files
     *
     * @return array with arrays of plugin configs
     */
    public function getCascade()
    {
        return $this->pluginCascade;
    }

    /**
     * Read all installed plugins and their current enabled state
     */
    protected function populateMasterList()
    {
        if ($dh = @opendir(DOKU_PLUGIN)) {
            $all_plugins = array();
            while (false !== ($plugin = readdir($dh))) {
                if ($plugin[0] === '.') continue;               // skip hidden entries
                if (is_file(DOKU_PLUGIN . $plugin)) continue;    // skip files, we're only interested in directories

                if (array_key_exists($plugin, $this->masterList) && $this->masterList[$plugin] == 0) {
                    $all_plugins[$plugin] = 0;

                } elseif (array_key_exists($plugin, $this->masterList) && $this->masterList[$plugin] == 1) {
                    $all_plugins[$plugin] = 1;
                } else {
                    $all_plugins[$plugin] = 1;
                }
            }
            $this->masterList = $all_plugins;
            if (!file_exists($this->lastLocalConfigFile)) {
                $this->saveList(true);
            }
        }
    }

    /**
     * Includes the plugin config $files
     * and returns the entries of the $plugins array set in these files
     *
     * @param array $files list of files to include, latter overrides previous
     * @return array with entries of the $plugins arrays of the included files
     */
    protected function checkRequire($files)
    {
        $plugins = array();
        foreach ($files as $file) {
            if (file_exists($file)) {
                include_once($file);
            }
        }
        return $plugins;
    }

    /**
     * Save the current list of plugins
     *
     * @param bool $forceSave ;
     *              false to save only when config changed
     *              true to always save
     * @return bool  true saving succeed, false saving failed
     */
    protected function saveList($forceSave = false)
    {
        global $conf;

        if (empty($this->masterList)) return false;

        // Rebuild list of local settings
        $local_plugins = $this->rebuildLocal();
        if ($local_plugins != $this->pluginCascade['local'] || $forceSave) {
            $file = $this->lastLocalConfigFile;
            $out = "<?php\n/*\n * Local plugin enable/disable settings\n" .
                " * Auto-generated through plugin/extension manager\n *\n" .
                " * NOTE: Plugins will not be added to this file unless there " .
                "is a need to override a default setting. Plugins are\n" .
                " *       enabled by default.\n */\n";
            foreach ($local_plugins as $plugin => $value) {
                $out .= "\$plugins['$plugin'] = $value;\n";
            }
            // backup current file (remove any existing backup)
            if (file_exists($file)) {
                $backup = $file . '.bak';
                if (file_exists($backup)) @unlink($backup);
                if (!@copy($file, $backup)) return false;
                if (!empty($conf['fperm'])) chmod($backup, $conf['fperm']);
            }
            //check if can open for writing, else restore
            return io_saveFile($file, $out);
        }
        return false;
    }

    /**
     * Rebuild the set of local plugins
     *
     * @return array array of plugins to be saved in end($config_cascade['plugins']['local'])
     */
    protected function rebuildLocal()
    {
        //assign to local variable to avoid overwriting
        $backup = $this->masterList;
        //Can't do anything about protected one so rule them out completely
        $local_default = array_diff_key($backup, $this->pluginCascade['protected']);
        //Diff between local+default and default
        //gives us the ones we need to check and save
        $diffed_ones = array_diff_key($local_default, $this->pluginCascade['default']);
        //The ones which we are sure of (list of 0s not in default)
        $sure_plugins = array_filter($diffed_ones, array($this, 'negate'));
        //the ones in need of diff
        $conflicts = array_diff_key($local_default, $diffed_ones);
        //The final list
        return array_merge($sure_plugins, array_diff_assoc($conflicts, $this->pluginCascade['default']));
    }

    /**
     * Build the list of plugins and cascade
     *
     */
    protected function loadConfig()
    {
        global $config_cascade;
        foreach (array('default', 'protected') as $type) {
            if (array_key_exists($type, $config_cascade['plugins'])) {
                $this->pluginCascade[$type] = $this->checkRequire($config_cascade['plugins'][$type]);
            }
        }
        $local = $config_cascade['plugins']['local'];
        $this->lastLocalConfigFile = array_pop($local);
        $this->pluginCascade['local'] = $this->checkRequire(array($this->lastLocalConfigFile));
        if (is_array($local)) {
            $this->pluginCascade['default'] = array_merge(
                $this->pluginCascade['default'],
                $this->checkRequire($local)
            );
        }
        $this->masterList = array_merge(
            $this->pluginCascade['default'],
            $this->pluginCascade['local'],
            $this->pluginCascade['protected']
        );
    }

    /**
     * Returns a list of available plugin components of given type
     *
     * @param string $type plugin_type name; the type of plugin to return,
     * @param bool $enabled true to return enabled plugins,
     *                          false to return disabled plugins
     * @return array of plugin components of requested type
     */
    protected function getListByType($type, $enabled)
    {
        $master_list = $enabled
            ? array_keys(array_filter($this->masterList))
            : array_keys(array_filter($this->masterList, array($this, 'negate')));
        $plugins = array();

        foreach ($master_list as $plugin) {

            if (file_exists(DOKU_PLUGIN . "$plugin/$type.php")) {
                $plugins[] = $plugin;
                continue;
            }

            $typedir = DOKU_PLUGIN . "$plugin/$type/";
            if (is_dir($typedir)) {
                if ($dp = opendir($typedir)) {
                    while (false !== ($component = readdir($dp))) {
                        if (strpos($component, '.') === 0 || strtolower(substr($component, -4)) !== '.php') continue;
                        if (is_file($typedir . $component)) {
                            $plugins[] = $plugin . '_' . substr($component, 0, -4);
                        }
                    }
                    closedir($dp);
                }
            }

        }//foreach

        return $plugins;
    }

    /**
     * Split name in a plugin name and a component name
     *
     * @param string $name
     * @return array with
     *              - plugin name
     *              - and component name when available, otherwise empty string
     */
    protected function splitName($name)
    {
        if (!isset($this->masterList[$name])) {
            return explode('_', $name, 2);
        }

        return array($name, '');
    }

    /**
     * Returns inverse boolean value of the input
     *
     * @param mixed $input
     * @return bool inversed boolean value of input
     */
    protected function negate($input)
    {
        return !(bool)$input;
    }
}
