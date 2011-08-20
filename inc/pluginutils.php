<?php
/**
 * Utilities for handling plugins
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

// plugin related constants
if(!defined('DOKU_PLUGIN'))  define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

/**
 * Original plugin functions, remain for backwards compatibility
 */
function plugin_list($type='',$all=false) {
    global $plugin_controller;
    return $plugin_controller->getList($type,$all);
}
function plugin_load($type,$name,$new=false,$disabled=false) {
    global $plugin_controller;
    return $plugin_controller->load($type,$name,$new,$disabled);
}
function plugin_isdisabled($plugin) {
    global $plugin_controller;
    return $plugin_controller->isdisabled($plugin);
}
function plugin_enable($plugin) {
    global $plugin_controller;
    return $plugin_controller->enable($plugin);
}
function plugin_disable($plugin) {
    global $plugin_controller;
    return $plugin_controller->disable($plugin);
}
function plugin_directory($plugin) {
    global $plugin_controller;
    return $plugin_controller->get_directory($plugin);
}
function plugin_getcascade() {
    global $plugin_controller;
    return $plugin_controller->getCascade();
}
/**
 * return a list (name & type) of all the component plugins that make up this plugin
 *
 */
function get_plugin_components($plugin) {
    global $plugin_types;
    static $plugins;
    if(empty($plugins[$plugin])) {
        $components = array();
        $path = DOKU_PLUGIN.plugin_directory($plugin).'/';

        foreach ($plugin_types as $type) {
            if (@file_exists($path.$type.'.php')) { $components[] = array('name'=>$plugin, 'type'=>$type); continue; }

            if ($dh = @opendir($path.$type.'/')) {
                while (false !== ($cp = readdir($dh))) {
                    if ($cp == '.' || $cp == '..' || strtolower(substr($cp,-4)) != '.php') continue;

                    $components[] = array('name'=>$plugin.'_'.substr($cp, 0, -4), 'type'=>$type);
                }
                closedir($dh);
            }
        }
        $plugins[$plugin] = $components;
    }
    return $plugins[$plugin];
}
