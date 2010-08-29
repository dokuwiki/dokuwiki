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

    var $list_enabled = array();
    var $list_disabled = array();
    var $list_bytype = array();

    function Doku_Plugin_Controller() {
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
            return $all ? array_merge($this->list_enabled,$this->list_disabled) : $this->list_enabled;
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
    function &load($type,$name,$new=false,$disabled=false){
        //we keep all loaded plugins available in global scope for reuse
        global $DOKU_PLUGINS;

        list($plugin,$component) = $this->_splitName($name);

        // check if disabled
        if(!$disabled && $this->isdisabled($plugin)){
            return null;
        }

        //plugin already loaded?
        if(!empty($DOKU_PLUGINS[$type][$name])){
            if ($new || !$DOKU_PLUGINS[$type][$name]->isSingleton()) {
                $class = $type.'_plugin_'.$name;
                return class_exists($class) ? new $class : null;
            } else {
                return $DOKU_PLUGINS[$type][$name];
            }
        }

        //try to load the wanted plugin file
        $dir = $this->get_directory($plugin);
        $file = $component ? "$type/$component.php" : "$type.php";

        if(!is_file(DOKU_PLUGIN."$dir/$file")){
            return null;
        }

        if (!include_once(DOKU_PLUGIN."$dir/$file")) {
            return null;
        }

        //construct class and instantiate
        $class = $type.'_plugin_'.$name;
        if (!class_exists($class)) return null;

        $DOKU_PLUGINS[$type][$name] = new $class;
        return $DOKU_PLUGINS[$type][$name];
    }

    function isdisabled($plugin) {
        return (array_search($plugin, $this->list_enabled) === false);
    }

    function enable($plugin) {
        if (array_search($plugin, $this->list_disabled) !== false) {
            return @unlink(DOKU_PLUGIN.$plugin.'/disabled');
        }
        return false;
    }

    function disable($plugin) {
        if (array_search($plugin, $this->list_enabled) !== false) {
            return @touch(DOKU_PLUGIN.$plugin.'/disabled');
        }
        return false;
    }

    function get_directory($plugin) {
        return $plugin;
    }

    function _populateMasterList() {
        if ($dh = opendir(DOKU_PLUGIN)) {
            while (false !== ($plugin = readdir($dh))) {
                if ($plugin[0] == '.') continue;               // skip hidden entries
                if (is_file(DOKU_PLUGIN.$plugin)) continue;    // skip files, we're only interested in directories

                if (substr($plugin,-9) == '.disabled') {
                    // the plugin was disabled by rc2009-01-26
                    // disabling mechanism was changed back very soon again
                    // to keep everything simple we just skip the plugin completely
                }elseif(@file_exists(DOKU_PLUGIN.$plugin.'/disabled')){
                    $this->list_disabled[] = $plugin;
                } else {
                    $this->list_enabled[] = $plugin;
                }
            }
        }
    }

    function _getListByType($type, $enabled) {
        $master_list = $enabled ? $this->list_enabled : $this->list_disabled;

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
        if (array_search($name, $this->list_enabled + $this->list_disabled) === false) {
            return explode('_',$name,2);
        }

        return array($name,'');
    }

}
