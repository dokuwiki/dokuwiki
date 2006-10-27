<?php
/**
 * Utilities for handling plugins
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */
 
// plugin related constants
if(!defined('DOKU_PLUGIN'))  define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
$plugin_types = array('admin','syntax','action');
 
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
function plugin_list($type='',$all=false){
  $plugins = array();
  if ($dh = opendir(DOKU_PLUGIN)) {
    while (false !== ($plugin = readdir($dh))) {
      if ($plugin == '.' || $plugin == '..' || $plugin == 'tmp') continue;
      if (is_file(DOKU_PLUGIN.$plugin)) continue;
			
			// if required, skip disabled plugins
			if (!$all && plugin_isdisabled($plugin)) continue;

      if ($type=='' || @file_exists(DOKU_PLUGIN."$plugin/$type.php")){
          $plugins[] = $plugin;
      } else {
        if ($dp = @opendir(DOKU_PLUGIN."$plugin/$type/")) {
          while (false !== ($component = readdir($dp))) {
            if ($component == '.' || $component == '..' || strtolower(substr($component, -4)) != ".php") continue;
            if (is_file(DOKU_PLUGIN."$plugin/$type/$component")) {
              $plugins[] = $plugin.'_'.substr($component, 0, -4);
            }
          }
        closedir($dp);
        }
      }
    }
    closedir($dh);
  }
  return $plugins;
}

/**
 * Loads the given plugin and creates an object of it
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @param  $type string     type of plugin to load
 * @param  $name string     name of the plugin to load
 * @return objectreference  the plugin object or null on failure
 */
function &plugin_load($type,$name){
  //we keep all loaded plugins available in global scope for reuse
  global $DOKU_PLUGINS;


  //plugin already loaded?
  if(!empty($DOKU_PLUGINS[$type][$name])){
    return $DOKU_PLUGINS[$type][$name];
  }

  //try to load the wanted plugin file
  if (@file_exists(DOKU_PLUGIN."$name/$type.php")){
    include_once(DOKU_PLUGIN."$name/$type.php");
  }else{
    list($plugin, $component) = preg_split("/_/",$name, 2);
    if (!$component || !include_once(DOKU_PLUGIN."$plugin/$type/$component.php")) {
        return null;
    }
  }

  //construct class and instantiate
  $class = $type.'_plugin_'.$name;
  if (!class_exists($class)) return null;

  $DOKU_PLUGINS[$type][$name] = new $class;
  return $DOKU_PLUGINS[$type][$name];
}

function plugin_isdisabled($name) { return @file_exists(DOKU_PLUGIN.$name.'/disabled'); }
function plugin_enable($name) { return @unlink(DOKU_PLUGIN.$name.'/disabled'); }
function plugin_disable($name) { return @touch(DOKU_PLUGIN.$name.'/disabled'); }
