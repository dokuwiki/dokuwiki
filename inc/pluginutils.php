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
