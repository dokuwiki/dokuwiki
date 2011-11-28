<?php

if (!defined('DOKU_INC')) die();

/**
 * This class provides information about remote access to the wiki.
 *
 * == Types of methods ==
 * There are two types of remote methods. The first is the core methods.
 * These are always available and provided by dokuwiki.
 * The other is plugin methods. These are provided by remote plugins.
 *
 * == Information structure ==
 * The information about methods will be given in an array with the following structure:
 * array(
 *     'method.name' => array(
 *          'args' => array(
 *              'type' => 'string|int|...',
 *          )
 *          'return' => 'type'
 *     )
 * )
 *
 * plugin names are formed the following:
 *   core methods begin by a 'dokuwiki' or 'wiki' followed by a . and the method name itself.
 *   i.e.: dokuwiki.version or wiki.getPage
 *
 * plugin methods are formed like 'plugin.<plugin name>.<method name>'.
 * i.e.: plugin.clock.getTime or plugin.clock_gmt.getTime
 *
 *
 *
 * @throws RemoteException
 */
class RemoteAPI {

    /**
     * @var array remote methods provided by dokuwiki.
     */
    private $coreMethods = array();

    /**
     * @var array remote methods provided by dokuwiki plugins - will be filled lazy via
     * {@see RemoteAPI#getPluginMethods}
     */
    private $pluginMethods = null;

    /**
     * Get all available methods with remote access.
     *
     * @return array with information to all available methods
     */
    public function getMethods() {
        $this->forceAccess();
        return array_merge($this->getCoreMethods(), $this->getPluginMethods());
    }

    /**
     * call a method via remote api.
     *
     * @param string $method name of the method to call.
     * @param array $args arguments to pass to the given method
     * @return mixed result of method call, must be a primitive type.
     */
    public function call($method, $args) {
        $this->forceAccess();
        $method = explode('.', $method);
        if ($method[0] === 'plugin') {
            $plugin = plugin_load('remote', $method[1]);
            if (!$plugin) {
                throw new RemoteException('Method unavailable');
            }
            return call_user_func_array(array($plugin, $method[2]), $args);
        } else {
            // TODO call core method
        }

    }

    /**
     * @return bool true if the current user has access to remote api.
     */
    public function hasAccess() {
        global $conf;
        if (!isset($conf['remote'])) {
            return false;
        }
        return $conf['remote'];
    }

    /**
     * @throws RemoteException On denied access.
     * @return void
     */
    public function forceAccess() {
        if (!$this->hasAccess()) {
            throw new RemoteException('Access denied');
        }
    }

    /**
     * @return array all plugin methods.
     */
    public function getPluginMethods() {
        if ($this->pluginMethods === null) {
            $this->pluginMethods = array();
            $plugins = plugin_list('remote');

            foreach ($plugins as $pluginName) {
                $plugin = plugin_load('remote', $pluginName);
                if (!is_subclass_of($plugin, 'DokuWiki_Remote_Plugin')) {
                    throw new RemoteException("Plugin $pluginName dose not implement DokuWiki_Remote_Plugin");
                }

                $methods = $plugin->_getMethods();
                foreach ($methods as $method => $meta) {
                    $this->pluginMethods["plugin.$pluginName.$method"] = $meta;
                }
            }
        }
        return $this->pluginMethods;
    }

    /**
     * @return array all core methods.
     */
    public function getCoreMethods() {
        return $this->coreMethods;
    }
}


class RemoteException extends Exception {}
class RemoteAccessDenied extends RemoteException {}