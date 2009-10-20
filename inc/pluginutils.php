<?php
/**
 * Utilities for handling plugins
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

// plugin related constants
if(!defined('DOKU_PLUGIN'))  define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_INC.'inc/plugincontroller.class.php');

$plugin_types = array('admin','syntax','action','renderer', 'helper');

global $plugin_controller_class, $plugin_controller;
if (empty($plugin_controller_class)) $plugin_controller_class = 'Doku_Plugin_Controller';

$plugin_controller = new $plugin_controller_class();

/**
 * Original plugin functions, remain for backwards compatibility
 */
function plugin_list($type='',$all=false) {
    global $plugin_controller;
    return $plugin_controller->getList($type,$all);
}
function &plugin_load($type,$name,$new=false) {
    global $plugin_controller;
    return $plugin_controller->load($type,$name,$new);
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

