<?php

use dokuwiki\Extension\RemotePlugin;

/**
 * For testing manual method descriptions
 *
 * @author Dominik Eckelmann <deckelmann@gmail.com>
 */
class remote_plugin_testing_manual extends RemotePlugin
{
    /** @inheritdoc */
    function _getMethods()
    {
        return array(
            'method1' => array(
                'args' => array(),
                'return' => 'void',
            ),
            'methodString' => array(
                'args' => array(),
                'return' => 'string',
            ),
            'method2' => array(
                'args' => array('string', 'int'),
                'return' => 'array',
                'name' => 'method2',
            ),
            'method2ext' => array(
                'args' => array('string', 'int', 'bool'),
                'return' => 'array',
                'name' => 'method2',
            ),
            'publicCall' => array(
                'args' => array(),
                'return' => 'boolean',
                'doc' => 'testing for public access',
                'name' => 'publicCall',
                'public' => 1,
            ),
        );
    }

    /** @inheritdoc */
    function method1()
    {
        return null;
    }

    /** @inheritdoc */
    function methodString()
    {
        return 'success';
    }

    /** @inheritdoc */
    function method2($str, $int, $bool = false)
    {
        return array($str, $int, $bool);
    }

    /** @inheritdoc */
    function publicCall()
    {
        return true;
    }
}
