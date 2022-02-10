<?php
namespace IXR\Server;

use IXR\Message\Error;

/**
 * Extension of the {@link Server} class to easily wrap objects.
 *
 * Class is designed to extend the existing XML-RPC server to allow the
 * presentation of methods from a variety of different objects via an
 * XML-RPC server.
 * It is intended to assist in organization of your XML-RPC methods by allowing
 * you to "write once" in your existing model classes and present them.
 *
 * @author Jason Stirk <jstirk@gmm.com.au>
 * @version 1.0.1 19Apr2005 17:40 +0800
 * @copyright Copyright (c) 2005 Jason Stirk
 * @package IXR
 */
class ClassServer extends Server
{

    private $_objects;
    private $_delim;

    public function __construct($delim = '.', $wait = false)
    {
        parent::__construct([], false, $wait);
        $this->_delim = $delim;
        $this->_objects = [];
    }

    public function addMethod($rpcName, $functionName)
    {
        $this->callbacks[$rpcName] = $functionName;
    }

    public function registerObject($object, $methods, $prefix = null)
    {
        if (is_null($prefix)) {
            $prefix = get_class($object);
        }
        $this->_objects[$prefix] = $object;

        // Add to our callbacks array
        foreach ($methods as $method) {
            if (is_array($method)) {
                $targetMethod = $method[0];
                $method = $method[1];
            } else {
                $targetMethod = $method;
            }
            $this->callbacks[$prefix . $this->_delim . $method] = [$prefix, $targetMethod];
        }
    }

    public function call($methodname, $args)
    {
        if (!$this->hasMethod($methodname)) {
            return new Error(-32601, 'server error. requested method ' . $methodname . ' does not exist.');
        }
        $method = $this->callbacks[$methodname];

        // Perform the callback and send the response
        if (count($args) == 1) {
            // If only one paramater just send that instead of the whole array
            $args = $args[0];
        }

        // See if this method comes from one of our objects or maybe self
        if (is_array($method) || (substr($method, 0, 5) == 'this:')) {
            if (is_array($method)) {
                $object = $this->_objects[$method[0]];
                $method = $method[1];
            } else {
                $object = $this;
                $method = substr($method, 5);
            }

            // It's a class method - check it exists
            if (!method_exists($object, $method)) {
                return new Error(-32601, 'server error. requested class method "' . $method . '" does not exist.');
            }

            // Call the method
            $result = $object->$method($args);
        } else {
            // It's a function - does it exist?
            if (!function_exists($method)) {
                return new Error(-32601, 'server error. requested function "' . $method . '" does not exist.');
            }

            // Call the function
            $result = $method($args);
        }
        return $result;
    }
}
