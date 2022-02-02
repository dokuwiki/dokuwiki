<?php
/**
 * Utilities for handling plugins
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

// plugin related constants
use dokuwiki\Extension\AdminPlugin;
use dokuwiki\Extension\PluginController;
use dokuwiki\Extension\PluginInterface;

if(!defined('DOKU_PLUGIN'))  define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
// note that only [a-z0-9]+ is officially supported,
// this is only to support plugins that don't follow these conventions, too
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
function plugin_list($type='',$all=false)
{
    /** @var $plugin_controller PluginController */
    global $plugin_controller;
    $plugins = $plugin_controller->getList($type,$all);
    sort($plugins, SORT_NATURAL|SORT_FLAG_CASE);
    return $plugins;
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
 * @return PluginInterface|null  the plugin object or null on failure
 */
function plugin_load($type,$name,$new=false,$disabled=false)
{
    /** @var $plugin_controller PluginController */
    global $plugin_controller;
    return $plugin_controller->load($type,$name,$new,$disabled);
}

/**
 * Whether plugin is disabled
 *
 * @param string $plugin name of plugin
 * @return bool true disabled, false enabled
 */
function plugin_isdisabled($plugin)
{
    /** @var $plugin_controller PluginController */
    global $plugin_controller;
    return !$plugin_controller->isEnabled($plugin);
}

/**
 * Enable the plugin
 *
 * @param string $plugin name of plugin
 * @return bool true saving succeed, false saving failed
 */
function plugin_enable($plugin)
{
    /** @var $plugin_controller PluginController */
    global $plugin_controller;
    return $plugin_controller->enable($plugin);
}

/**
 * Disable the plugin
 *
 * @param string $plugin name of plugin
 * @return bool  true saving succeed, false saving failed
 */
function plugin_disable($plugin)
{
    /** @var $plugin_controller PluginController */
    global $plugin_controller;
    return $plugin_controller->disable($plugin);
}

/**
 * Returns directory name of plugin
 *
 * @param string $plugin name of plugin
 * @return string name of directory
 * @deprecated 2018-07-20
 */
function plugin_directory($plugin)
{
    dbg_deprecated('$plugin directly');
    return $plugin;
}

/**
 * Returns cascade of the config files
 *
 * @return array with arrays of plugin configs
 */
function plugin_getcascade()
{
    /** @var $plugin_controller PluginController */
    global $plugin_controller;
    return $plugin_controller->getCascade();
}


/**
 * Return the currently operating admin plugin or null
 * if not on an admin plugin page
 *
 * @return Doku_Plugin_Admin
 */
function plugin_getRequestAdminPlugin()
{
    static $admin_plugin = false;
    global $ACT,$INPUT,$INFO;

    if ($admin_plugin === false) {
        if (($ACT == 'admin') && ($page = $INPUT->str('page', '', true)) != '') {
            $pluginlist = plugin_list('admin');
            if (in_array($page, $pluginlist)) {
                // attempt to load the plugin
                /** @var $admin_plugin AdminPlugin */
                $admin_plugin = plugin_load('admin', $page);
                // verify
                if ($admin_plugin && !$admin_plugin->isAccessibleByCurrentUser()) {
                    $admin_plugin = null;
                    $INPUT->remove('page');
                    msg('For admins only',-1);
                }
            }
        }
    }

    return $admin_plugin;
}
