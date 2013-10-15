<?php
/**
 * Utilities for handling plugins
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

// plugin related constants
if(!defined('DOKU_PLUGIN'))  define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
// note that only [a-z0-9]+ is officially supported, this is only to support plugins that don't follow these conventions, too
if(!defined('DOKU_PLUGIN_NAME_REGEX')) define('DOKU_PLUGIN_NAME_REGEX', '[a-zA-Z0-9\x7f-\xff]+');

/**
 * Original plugin functions, remain for backwards compatibility
 */

/**
 * Return list of available plugins
 *
 * @param string $type type of plugins; empty string for all
 * @param bool $all; true to retrieve all, false to retrieve only enabled plugins
 * @return array with plugin names or plugin component names
 */
function plugin_list($type='',$all=false) {
    /** @var $plugin_controller Doku_Plugin_Controller */
    global $plugin_controller;
    return $plugin_controller->getList($type,$all);
}

/**
 * Returns plugin object
 * Returns only new instances of a plugin when $new is true or if plugin is not Singleton,
 * otherwise an already loaded instance.
 *
 * @param  $type     string type of plugin to load
 * @param  $name     string name of the plugin to load
 * @param  $new      bool   true to return a new instance of the plugin, false to use an already loaded instance
 * @param  $disabled bool   true to load even disabled plugins
 * @return DokuWiki_Plugin|DokuWiki_Syntax_Plugin|null  the plugin object or null on failure
 */
function plugin_load($type,$name,$new=false,$disabled=false) {
    /** @var $plugin_controller Doku_Plugin_Controller */
    global $plugin_controller;
    return $plugin_controller->load($type,$name,$new,$disabled);
}

/**
 * Whether plugin is disabled
 *
 * @param string $plugin name of plugin
 * @return bool; true disabled, false enabled
 */
function plugin_isdisabled($plugin) {
    /** @var $plugin_controller Doku_Plugin_Controller */
    global $plugin_controller;
    return $plugin_controller->isdisabled($plugin);
}

/**
 * Enable the plugin
 *
 * @param string $plugin name of plugin
 * @return bool; true saving succeed, false saving failed
 */
function plugin_enable($plugin) {
    /** @var $plugin_controller Doku_Plugin_Controller */
    global $plugin_controller;
    return $plugin_controller->enable($plugin);
}

/**
 * Disable the plugin
 *
 * @param string $plugin name of plugin
 * @return bool; true saving succeed, false saving failed
 */
function plugin_disable($plugin) {
    /** @var $plugin_controller Doku_Plugin_Controller */
    global $plugin_controller;
    return $plugin_controller->disable($plugin);
}

/**
 * Returns directory name of plugin
 *
 * @param string $plugin name of plugin
 * @return string name of directory
 */
function plugin_directory($plugin) {
    /** @var $plugin_controller Doku_Plugin_Controller */
    global $plugin_controller;
    return $plugin_controller->get_directory($plugin);
}

/**
 * Returns cascade of the config files
 *
 * @return array with arrays of plugin configs
 */
function plugin_getcascade() {
    /** @var $plugin_controller Doku_Plugin_Controller */
    global $plugin_controller;
    return $plugin_controller->getCascade();
}
