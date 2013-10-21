<?php
/**
 * Class to encapsulate access to dokuwiki plugins
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 */

// plugin related constants
if(!defined('DOKU_PLUGIN'))  define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

class Doku_Plugin_Controller {

    protected $list_bytype = array();
    protected $tmp_plugins = array();
    protected $plugin_cascade = array('default'=>array(),'local'=>array(),'protected'=>array());
    protected $last_local_config_file = '';

    /**
     * Populates the master list of plugins
     */
    public function __construct() {
        $this->loadConfig();
        $this->_populateMasterList();
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
    public function getList($type='',$all=false){

        // request the complete list
        if (!$type) {
            return $all ? array_keys($this->tmp_plugins) : array_keys(array_filter($this->tmp_plugins));
        }

        if (!isset($this->list_bytype[$type]['enabled'])) {
            $this->list_bytype[$type]['enabled'] = $this->_getListByType($type,true);
        }
        if ($all && !isset($this->list_bytype[$type]['disabled'])) {
            $this->list_bytype[$type]['disabled'] = $this->_getListByType($type,false);
        }

        return $all ? array_merge($this->list_bytype[$type]['enabled'],$this->list_bytype[$type]['disabled']) : $this->list_bytype[$type]['enabled'];
    }

    /**
     * Loads the given plugin and creates an object of it
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * @param  $type     string type of plugin to load
     * @param  $name     string name of the plugin to load
     * @param  $new      bool   true to return a new instance of the plugin, false to use an already loaded instance
     * @param  $disabled bool   true to load even disabled plugins
     * @return DokuWiki_Plugin|DokuWiki_Syntax_Plugin|null  the plugin object or null on failure
     */
    public function load($type,$name,$new=false,$disabled=false){

        //we keep all loaded plugins available in global scope for reuse
        global $DOKU_PLUGINS;

        list($plugin,$component) = $this->_splitName($name);

        // check if disabled
        if(!$disabled && $this->isdisabled($plugin)){
            return null;
        }

        $class = $type.'_plugin_'.$name;

        //plugin already loaded?
        if(!empty($DOKU_PLUGINS[$type][$name])){
            if ($new || !$DOKU_PLUGINS[$type][$name]->isSingleton()) {
                return class_exists($class, true) ? new $class : null;
            } else {
                return $DOKU_PLUGINS[$type][$name];
            }
        }

        //construct class and instantiate
        if (!class_exists($class, true)) {

            # the plugin might be in the wrong directory
            $dir = $this->get_directory($plugin);
            $inf = confToHash(DOKU_PLUGIN."$dir/plugin.info.txt");
            if($inf['base'] && $inf['base'] != $plugin){
                msg(sprintf("Plugin installed incorrectly. Rename plugin directory '%s' to '%s'.", hsc($plugin), hsc($inf['base'])), -1);
            } elseif (preg_match('/^'.DOKU_PLUGIN_NAME_REGEX.'$/', $plugin) !== 1) {
                msg(sprintf("Plugin name '%s' is not a valid plugin name, only the characters a-z and 0-9 are allowed. ".
                                'Maybe the plugin has been installed in the wrong directory?', hsc($plugin)), -1);
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
     * @return bool; true disabled, false enabled
     */
    public function isdisabled($plugin) {
        return empty($this->tmp_plugins[$plugin]);
    }

    /**
     * Disable the plugin
     *
     * @param string $plugin name of plugin
     * @return bool; true saving succeed, false saving failed
     */
    public function disable($plugin) {
        if(array_key_exists($plugin,$this->plugin_cascade['protected'])) return false;
        $this->tmp_plugins[$plugin] = 0;
        return $this->saveList();
    }

    /**
     * Enable the plugin
     *
     * @param string $plugin name of plugin
     * @return bool; true saving succeed, false saving failed
     */
    public function enable($plugin) {
        if(array_key_exists($plugin,$this->plugin_cascade['protected'])) return false;
        $this->tmp_plugins[$plugin] = 1;
        return $this->saveList();
    }

    /**
     * Returns directory name of plugin
     *
     * @param string $plugin name of plugin
     * @return string name of directory
     */
    public function get_directory($plugin) {
        return $plugin;
    }

    /**
     * Returns cascade of the config files
     *
     * @return array with arrays of plugin configs
     */
    public function getCascade() {
        return $this->plugin_cascade;
    }

    protected function _populateMasterList() {
        global $conf;

        if ($dh = @opendir(DOKU_PLUGIN)) {
            $all_plugins = array();
            while (false !== ($plugin = readdir($dh))) {
                if ($plugin[0] == '.') continue;               // skip hidden entries
                if (is_file(DOKU_PLUGIN.$plugin)) continue;    // skip files, we're only interested in directories

                if (substr($plugin,-9) == '.disabled') {
                    // the plugin was disabled by rc2009-01-26
                    // disabling mechanism was changed back very soon again
                    // to keep everything simple we just skip the plugin completely
                    continue;
                } elseif (@file_exists(DOKU_PLUGIN.$plugin.'/disabled')) {
                    // treat this as a default disabled plugin(over-rideable by the plugin manager)
                    // deprecated 2011-09-10 (usage of disabled files)
                    if (empty($this->plugin_cascade['local'][$plugin])) {
                        $all_plugins[$plugin] = 0;
                    } else {
                        $all_plugins[$plugin] = 1;
                    }
                    $this->plugin_cascade['default'][$plugin] = 0;

                } elseif ((array_key_exists($plugin,$this->tmp_plugins) && $this->tmp_plugins[$plugin] == 0) ||
                          ($plugin === 'plugin' && isset($conf['pluginmanager']) && !$conf['pluginmanager'])){
                    $all_plugins[$plugin] = 0;

                } elseif ((array_key_exists($plugin,$this->tmp_plugins) && $this->tmp_plugins[$plugin] == 1)) {
                    $all_plugins[$plugin] = 1;
                } else {
                    $all_plugins[$plugin] = 1;
                }
            }
            $this->tmp_plugins = $all_plugins;
            if (!file_exists($this->last_local_config_file)) {
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
    protected function checkRequire($files) {
        $plugins = array();
        foreach($files as $file) {
            if(file_exists($file)) {
                include_once($file);
            }
        }
        return $plugins;
    }

    /**
     * Save the current list of plugins
     *
     * @param bool $forceSave;
     *              false to save only when config changed
     *              true to always save
     * @return bool; true saving succeed, false saving failed
     */
    protected function saveList($forceSave = false) {
        global $conf;

        if (empty($this->tmp_plugins)) return false;

        // Rebuild list of local settings
        $local_plugins = $this->rebuildLocal();
        if($local_plugins != $this->plugin_cascade['local'] || $forceSave) {
            $file = $this->last_local_config_file;
            $out = "<?php\n/*\n * Local plugin enable/disable settings\n * Auto-generated through plugin/extension manager\n *\n".
                   " * NOTE: Plugins will not be added to this file unless there is a need to override a default setting. Plugins are\n".
                   " *       enabled by default, unless having a 'disabled' file in their plugin folder.\n */\n";
            foreach ($local_plugins as $plugin => $value) {
                $out .= "\$plugins['$plugin'] = $value;\n";
            }
            // backup current file (remove any existing backup)
            if (@file_exists($file)) {
                $backup = $file.'.bak';
                if (@file_exists($backup)) @unlink($backup);
                if (!@copy($file,$backup)) return false;
                if (!empty($conf['fperm'])) chmod($backup, $conf['fperm']);
            }
            //check if can open for writing, else restore
            return io_saveFile($file,$out);
        }
        return false;
    }

    /**
     * Rebuild the set of local plugins
     *
     * @return array array of plugins to be saved in end($config_cascade['plugins']['local'])
     */
    protected function rebuildLocal() {
        //assign to local variable to avoid overwriting
        $backup = $this->tmp_plugins;
        //Can't do anything about protected one so rule them out completely
        $local_default = array_diff_key($backup,$this->plugin_cascade['protected']);
        //Diff between local+default and default
        //gives us the ones we need to check and save
        $diffed_ones = array_diff_key($local_default,$this->plugin_cascade['default']);
        //The ones which we are sure of (list of 0s not in default)
        $sure_plugins = array_filter($diffed_ones,array($this,'negate'));
        //the ones in need of diff
        $conflicts = array_diff_key($local_default,$diffed_ones);
        //The final list
        return array_merge($sure_plugins,array_diff_assoc($conflicts,$this->plugin_cascade['default']));
    }

    /**
     * Build the list of plugins and cascade
     * 
     */
    protected function loadConfig() {
        global $config_cascade;
        foreach(array('default','protected') as $type) {
            if(array_key_exists($type,$config_cascade['plugins']))
                $this->plugin_cascade[$type] = $this->checkRequire($config_cascade['plugins'][$type]);
        }
        $local = $config_cascade['plugins']['local'];
        $this->last_local_config_file = array_pop($local);
        $this->plugin_cascade['local'] = $this->checkRequire(array($this->last_local_config_file));
        if(is_array($local)) {
            $this->plugin_cascade['default'] = array_merge($this->plugin_cascade['default'],$this->checkRequire($local));
        }
        $this->tmp_plugins = array_merge($this->plugin_cascade['default'],$this->plugin_cascade['local'],$this->plugin_cascade['protected']);
    }

    /**
     * Returns a list of available plugin components of given type
     *
     * @param string $type, plugin_type name;
     *                      the type of plugin to return,
     * @param bool   $enabled;
     *                      true to return enabled plugins,
     *                      false to return disabled plugins
     *
     * @return array of plugin components of requested type
     */
    protected function _getListByType($type, $enabled) {
        $master_list = $enabled ? array_keys(array_filter($this->tmp_plugins)) : array_keys(array_filter($this->tmp_plugins,array($this,'negate')));

        $plugins = array();
        foreach ($master_list as $plugin) {
            $dir = $this->get_directory($plugin);

            if (@file_exists(DOKU_PLUGIN."$dir/$type.php")){
                $plugins[] = $plugin;
            } else {
                if ($dp = @opendir(DOKU_PLUGIN."$dir/$type/")) {
                    while (false !== ($component = readdir($dp))) {
                        if (substr($component,0,1) == '.' || strtolower(substr($component, -4)) != ".php") continue;
                        if (is_file(DOKU_PLUGIN."$dir/$type/$component")) {
                            $plugins[] = $plugin.'_'.substr($component, 0, -4);
                        }
                    }
                    closedir($dp);
                }
            }
        }

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
    protected function _splitName($name) {
        if (array_search($name, array_keys($this->tmp_plugins)) === false) {
            return explode('_',$name,2);
        }

        return array($name,'');
    }

    /**
     * Returns inverse boolean value of the input
     *
     * @param mixed $input
     * @return bool inversed boolean value of input
     */
    protected function negate($input) {
        return !(bool) $input;
    }
}
