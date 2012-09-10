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

    var $list_bytype = array();
    var $tmp_plugins = array();
    var $plugin_cascade = array('default'=>array(),'local'=>array(),'protected'=>array());
    var $last_local_config_file = '';

    /**
     * Populates the master list of plugins
     */
    function __construct() {
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
     * @return       array of plugin names
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function getList($type='',$all=false){

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
     * @return objectreference  the plugin object or null on failure
     */
    function load($type,$name,$new=false,$disabled=false){

        //we keep all loaded plugins available in global scope for reuse
        global $DOKU_PLUGINS;
        global $lang;

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
                msg(sprintf($lang['plugin_install_err'],hsc($plugin),hsc($inf['base'])),-1);
            }
            return null;
        }

        $DOKU_PLUGINS[$type][$name] = new $class;
        return $DOKU_PLUGINS[$type][$name];
    }

    function isdisabled($plugin) {
        return empty($this->tmp_plugins[$plugin]);
    }

    function disable($plugin) {
        if(array_key_exists($plugin,$this->plugin_cascade['protected'])) return false;
        $this->tmp_plugins[$plugin] = 0;
        return $this->saveList();
    }

    function enable($plugin) {
        if(array_key_exists($plugin,$this->plugin_cascade['protected'])) return false;
        $this->tmp_plugins[$plugin] = 1;
        return $this->saveList();
    }

    function get_directory($plugin) {
        return $plugin;
    }

    protected function _populateMasterList() {
        if ($dh = @opendir(DOKU_PLUGIN)) {
            $all_plugins = array();
            while (false !== ($plugin = readdir($dh))) {
                if ($plugin[0] == '.') continue;               // skip hidden entries
                if (is_file(DOKU_PLUGIN.$plugin)) continue;    // skip files, we're only interested in directories

                if (substr($plugin,-9) == '.disabled') {
                    // the plugin was disabled by rc2009-01-26
                    // disabling mechanism was changed back very soon again
                    // to keep everything simple we just skip the plugin completely
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

    protected function checkRequire($files) {
        $plugins = array();
        foreach($files as $file) {
            if(file_exists($file)) {
                @include_once($file);
            }
        }
        return $plugins;
    }

    function getCascade() {
        return $this->plugin_cascade;
    }

    /**
     * Save the current list of plugins
     */
    function saveList($forceSave = false) {
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
                if ($conf['fperm']) chmod($backup, $conf['fperm']);
            }
            //check if can open for writing, else restore
            return io_saveFile($file,$out);
        }
        return false;
    }

    /**
     * Rebuild the set of local plugins
     * @return array array of plugins to be saved in end($config_cascade['plugins']['local'])
     */
    function rebuildLocal() {
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
    function loadConfig() {
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

    function _getListByType($type, $enabled) {
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

    function _splitName($name) {
        if (array_search($name, array_keys($this->tmp_plugins)) === false) {
            return explode('_',$name,2);
        }

        return array($name,'');
    }
    function negate($input) {
        return !(bool) $input;
    }
}
