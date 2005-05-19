<?php
/**
 * Utilities for handling plugins
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

/**
 * Returns a list of available plugins of given type
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function plugin_list($type){
  $plugins = array();
  if ($dh = opendir(DOKU_PLUGIN)) {
    while (false !== ($file = readdir($dh))) {
      if ($file == '.' || $file == '..') continue;
      if (is_file(DOKU_PLUGIN.$file)) continue;

      if (@file_exists(DOKU_PLUGIN.$file.'/'.$type.'.php')){
        $plugins[] = $file;
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
 * @param  $type string 	type of plugin to load
 * @param  $name string 	name of the plugin to load
 * @param  $ref  ref      will contain the plugin object
 * @return boolean        plugin loading successful?
 */
function plugin_load($type,$name,&$ref){
  //we keep all loaded plugins available in global scope for reuse
  global $DOKU_PLUGINS;

	//plugin already loaded?
	if($DOKU_PLUGINS[$type][$name] != null){
		$ref = $DOKU_PLUGINS[$type][$name];
		return true;
	}

  //try to load the wanted plugin file
  if(!@include_once(DOKU_PLUGIN.$name.'/'.$type.'.php')){
    return false;
  }

  //construct class and instanciate
  $class = $type.'_plugin_'.$name;
  $DOKU_PLUGINS[$type][$name] = new $class;
  $ref = $DOKU_PLUGINS[$type][$name];
  return true;
}


