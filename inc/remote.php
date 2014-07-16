<?php

if (!defined('DOKU_INC')) die();

class RemoteException extends Exception {}
class RemoteAccessDeniedException extends RemoteException {}

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
 *     'method.remoteName' => array(
 *          'args' => array(
 *              'type eg. string|int|...|date|file',
 *          )
 *          'name' => 'method name in class',
 *          'return' => 'type',
 *          'public' => 1/0 - method bypass default group check (used by login)
 *          ['doc' = 'method documentation'],
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
 * @throws RemoteException
 */
class RemoteAPI {

    /**
     * @var RemoteAPICore
     */
    private $coreMethods = null;

    /**
     * @var array remote methods provided by dokuwiki plugins - will be filled lazy via
     * {@see RemoteAPI#getPluginMethods}
     */
    private $pluginMethods = null;

    /**
     * @var array contains custom calls to the api. Plugins can use the XML_CALL_REGISTER event.
     * The data inside is 'custom.call.something' => array('plugin name', 'remote method name')
     *
     * The remote method name is the same as in the remote name returned by _getMethods().
     */
    private $pluginCustomCalls = null;

    private $dateTransformation;
    private $fileTransformation;

    public function __construct() {
        $this->dateTransformation = array($this, 'dummyTransformation');
        $this->fileTransformation = array($this, 'dummyTransformation');
    }

    /**
     * Get all available methods with remote access.
     *
     * @return array with information to all available methods
     */
    public function getMethods() {
        return array_merge($this->getCoreMethods(), $this->getPluginMethods());
    }

    /**
     * call a method via remote api.
     *
     * @param string $method name of the method to call.
     * @param array $args arguments to pass to the given method
     * @return mixed result of method call, must be a primitive type.
     */
    public function call($method, $args = array()) {
        if ($args === null) {
            $args = array();
        }
        list($type, $pluginName, $call) = explode('.', $method, 3);
        if ($type === 'plugin') {
            return $this->callPlugin($pluginName, $method, $args);
        }
        if ($this->coreMethodExist($method)) {
            return $this->callCoreMethod($method, $args);
        }
        return $this->callCustomCallPlugin($method, $args);
    }

    private function coreMethodExist($name) {
        $coreMethods = $this->getCoreMethods();
        return array_key_exists($name, $coreMethods);
    }

    private function  callCustomCallPlugin($method, $args) {
        $customCalls = $this->getCustomCallPlugins();
        if (!array_key_exists($method, $customCalls)) {
            throw new RemoteException('Method does not exist', -32603);
        }
        $customCall = $customCalls[$method];
        return $this->callPlugin($customCall[0], $customCall[1], $args);
    }

    private function getCustomCallPlugins() {
        if ($this->pluginCustomCalls === null) {
            $data = array();
            trigger_event('RPC_CALL_ADD', $data);
            $this->pluginCustomCalls = $data;
        }
        return $this->pluginCustomCalls;
    }

    private function callPlugin($pluginName, $method, $args) {
        $plugin = plugin_load('remote', $pluginName);
        $methods = $this->getPluginMethods();
        if (!$plugin) {
            throw new RemoteException('Method does not exist', -32603);
        }
        $this->checkAccess($methods[$method]);
        $name = $this->getMethodName($methods, $method);
        return call_user_func_array(array($plugin, $name), $args);
    }

    private function callCoreMethod($method, $args) {
        $coreMethods = $this->getCoreMethods();
        $this->checkAccess($coreMethods[$method]);
        if (!isset($coreMethods[$method])) {
            throw new RemoteException('Method does not exist', -32603);
        }
        $this->checkArgumentLength($coreMethods[$method], $args);
        return call_user_func_array(array($this->coreMethods, $this->getMethodName($coreMethods, $method)), $args);
    }

    private function checkAccess($methodMeta) {
        if (!isset($methodMeta['public'])) {
            $this->forceAccess();
        } else{
            if ($methodMeta['public'] == '0') {
                $this->forceAccess();
            }
        }
    }

    private function checkArgumentLength($method, $args) {
        if (count($method['args']) < count($args)) {
            throw new RemoteException('Method does not exist - wrong parameter count.', -32603);
        }
    }

    private function getMethodName($methodMeta, $method) {
        if (isset($methodMeta[$method]['name'])) {
            return $methodMeta[$method]['name'];
        }
        $method = explode('.', $method);
        return $method[count($method)-1];
    }

    /**
     * @return bool true if the current user has access to remote api.
     */
    public function hasAccess() {
        global $conf;
        global $USERINFO;
        /** @var Input $INPUT */
        global $INPUT;

        if (!$conf['remote']) {
            return false;
        }
        if(!$conf['useacl']) {
            return true;
        }
        if(trim($conf['remoteuser']) == '') {
            return true;
        }

        return auth_isMember($conf['remoteuser'], $INPUT->server->str('REMOTE_USER'), (array) $USERINFO['grps']);
    }

    /**
     * @throws RemoteException On denied access.
     * @return void
     */
    public function forceAccess() {
        if (!$this->hasAccess()) {
            throw new RemoteAccessDeniedException('server error. not authorized to call method', -32604);
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
                    throw new RemoteException("Plugin $pluginName does not implement DokuWiki_Remote_Plugin");
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
     * @param RemoteAPICore $apiCore this parameter is used for testing. Here you can pass a non-default RemoteAPICore
     *                               instance. (for mocking)
     * @return array all core methods.
     */
    public function getCoreMethods($apiCore = null) {
        if ($this->coreMethods === null) {
            if ($apiCore === null) {
                $this->coreMethods = new RemoteAPICore($this);
            } else {
                $this->coreMethods = $apiCore;
            }
        }
        return $this->coreMethods->__getRemoteInfo();
    }

    public function toFile($data) {
        return call_user_func($this->fileTransformation, $data);
    }

    public function toDate($data) {
        return call_user_func($this->dateTransformation, $data);
    }

    public function dummyTransformation($data) {
        return $data;
    }

    public function setDateTransformation($dateTransformation) {
        $this->dateTransformation = $dateTransformation;
    }

    public function setFileTransformation($fileTransformation) {
        $this->fileTransformation = $fileTransformation;
    }
}
