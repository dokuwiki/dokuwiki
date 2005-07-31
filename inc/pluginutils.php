<?php
/**
 * Utilities for handling plugins
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

/**
 * prints needed HTML to include plugin CSS files
 */
function plugin_printCSS(){
  $plugins = plugin_list();
  foreach ($plugins as $p){
    $dir = "lib/plugins/$p/";
		if(@file_exists(DOKU_INC.$dir.'style.css')){
			print '  <link rel="stylesheet" type="text/css" href="'.DOKU_BASE.$dir.'style.css" />'."\n";
    }
		if(@file_exists(DOKU_INC.$dir.'screen.css')){
			print '  <link rel="stylesheet" media="screen" type="text/css" href="'.DOKU_BASE.$dir.'screen.css" />'."\n";
    }
		if(@file_exists(DOKU_INC.$dir.'print.css')){
			print '  <link rel="stylesheet" media="print" type="text/css" href="'.DOKU_BASE.$dir.'print.css" />'."\n";
    }
	}
} 

/**
 * Returns a list of available plugins of given type
 *
 * Returns all plugins if no type given
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function plugin_list($type=''){
  $plugins = array();
  if ($dh = opendir(DOKU_PLUGIN)) {
    while (false !== ($file = readdir($dh))) {
      if ($file == '.' || $file == '..') continue;
      if (is_file(DOKU_PLUGIN.$file)) continue;

	    if ($type=='' ||   @file_exists(DOKU_PLUGIN.$file.'/'.$type.'.php')){
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
 * @return object         the plugin object or null on failure
 */
function &plugin_load($type,$name){
  //we keep all loaded plugins available in global scope for reuse
  global $DOKU_PLUGINS;

	//plugin already loaded?
	if($DOKU_PLUGINS[$type][$name] != null){
		return $DOKU_PLUGINS[$type][$name];
	}

  //try to load the wanted plugin file
  if(!include_once(DOKU_PLUGIN.$name.'/'.$type.'.php')){
    return null;
  }

  //construct class and instanciate
  $class = $type.'_plugin_'.$name;
  $DOKU_PLUGINS[$type][$name] = new $class;
  return $DOKU_PLUGINS[$type][$name];
}


