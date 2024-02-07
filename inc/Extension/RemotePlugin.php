<?php

namespace dokuwiki\Extension;

use dokuwiki\Remote\Api;
use dokuwiki\Remote\ApiCall;
use ReflectionException;
use ReflectionMethod;

/**
 * Remote Plugin prototype
 *
 * Add functionality to the remote API in a plugin
 */
abstract class RemotePlugin extends Plugin
{
    private Api $api;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->api = new Api();
    }

    /**
     * Get all available methods with remote access.
     *
     * By default it exports all public methods of a remote plugin. Methods beginning
     * with an underscore are skipped.
     *
     * @return ApiCall[] Information about all provided methods. ('methodname' => ApiCall)
     * @throws ReflectionException
     */
    public function getMethods()
    {
        $result = [];

        $reflection = new \ReflectionClass($this);
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            // skip parent methods, only methods further down are exported
            $declaredin = $method->getDeclaringClass()->name;
            if ($declaredin === 'dokuwiki\Extension\Plugin' || $declaredin === 'dokuwiki\Extension\RemotePlugin') {
                continue;
            }
            $method_name = $method->name;
            if ($method_name[0] ===  '_') {
                continue;
            }
            if ($method_name === 'getMethods') {
                continue; // skip self, if overridden
            }

            // add to result
            $result[$method_name] = new ApiCall([$this, $method_name], 'plugins');
        }

        return $result;
    }

    /**
     * @deprecated 2023-11-30
     */
    public function _getMethods()
    {
        dbg_deprecated('getMethods()');
    }



    /**
     * @return Api
     */
    protected function getApi()
    {
        return $this->api;
    }
}
