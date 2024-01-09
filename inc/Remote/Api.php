<?php

namespace dokuwiki\Remote;

use dokuwiki\Extension\RemotePlugin;
use dokuwiki\Logger;
use dokuwiki\test\Remote\Mock\ApiCore as MockApiCore;

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
 */
class Api
{
    /** @var ApiCall[] core methods provided by dokuwiki */
    protected $coreMethods;

    /** @var ApiCall[] remote methods provided by dokuwiki plugins */
    protected $pluginMethods;

    /**
     * Get all available methods with remote access.
     *
     * @return ApiCall[] with information to all available methods
     */
    public function getMethods()
    {
        return array_merge($this->getCoreMethods(), $this->getPluginMethods());
    }

    /**
     * Collects all the core methods
     *
     * @param ApiCore|MockApiCore $apiCore this parameter is used for testing.
     *        Here you can pass a non-default RemoteAPICore instance. (for mocking)
     * @return ApiCall[] all core methods.
     */
    public function getCoreMethods($apiCore = null)
    {
        if (!$this->coreMethods) {
            if ($apiCore === null) {
                $this->coreMethods = (new LegacyApiCore())->getMethods();
            } else {
                $this->coreMethods = $apiCore->getMethods();
            }
        }
        return $this->coreMethods;
    }

    /**
     * Collects all the methods of the enabled Remote Plugins
     *
     * @return ApiCall[] all plugin methods.
     */
    public function getPluginMethods()
    {
        if ($this->pluginMethods) return $this->pluginMethods;

        $plugins = plugin_list('remote');
        foreach ($plugins as $pluginName) {
            /** @var RemotePlugin $plugin */
            $plugin = plugin_load('remote', $pluginName);
            if (!is_subclass_of($plugin, RemotePlugin::class)) {
                Logger::error("Remote Plugin $pluginName does not implement dokuwiki\Extension\RemotePlugin");
                continue;
            }

            try {
                $methods = $plugin->getMethods();
            } catch (\ReflectionException $e) {
                Logger::error(
                    "Remote Plugin $pluginName failed to return methods",
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                );
                continue;
            }

            foreach ($methods as $method => $call) {
                $this->pluginMethods["plugin.$pluginName.$method"] = $call;
            }
        }

        return $this->pluginMethods;
    }

    /**
     * Call a method via remote api.
     *
     * @param string $method name of the method to call.
     * @param array $args arguments to pass to the given method
     * @return mixed result of method call, must be a primitive type.
     * @throws RemoteException
     */
    public function call($method, $args = [])
    {
        if ($args === null) {
            $args = [];
        }

        // pre-flight checks
        $this->ensureApiIsEnabled();
        $methods = $this->getMethods();
        if (!isset($methods[$method])) {
            throw new RemoteException('Method does not exist', -32603);
        }
        $this->ensureAccessIsAllowed($methods[$method]);

        // invoke the ApiCall
        try {
            return $methods[$method]($args);
        } catch (\InvalidArgumentException | \ArgumentCountError $e) {
            throw new RemoteException($e->getMessage(), -32602);
        }
    }

    /**
     * Check that the API is generally enabled
     *
     * @return void
     * @throws RemoteException thrown when the API is disabled
     */
    public function ensureApiIsEnabled()
    {
        global $conf;
        if (!$conf['remote'] || trim($conf['remoteuser']) == '!!not set!!') {
            throw new AccessDeniedException('Server Error. API is not enabled in config.', -32604);
        }
    }

    /**
     * Check if the current user is allowed to call the given method
     *
     * @param ApiCall $method
     * @return void
     * @throws AccessDeniedException Thrown when the user is not allowed to call the method
     */
    public function ensureAccessIsAllowed(ApiCall $method)
    {
        global $conf;
        global $INPUT;
        global $USERINFO;

        if ($method->isPublic()) return; // public methods are always allowed
        if (!$conf['useacl']) return; // ACL is not enabled, so we can't check users
        if (trim($conf['remoteuser']) === '') return; // all users are allowed
        if (auth_isMember($conf['remoteuser'], $INPUT->server->str('REMOTE_USER'), (array)($USERINFO['grps'] ?? []))) {
            return; // user is allowed
        }

        // still here? no can do
        throw new AccessDeniedException('server error. not authorized to call method', -32604);
    }
}
