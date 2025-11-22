<?php

namespace easywiki\test\Remote\Mock;

use easywiki\Extension\RemotePlugin;
use easywiki\Remote\ApiCall;

class TestPlugin1 extends RemotePlugin
{
    function getMethods()
    {
        $methods = parent::getMethods(); // auto add all methods, then adjust

        $methods['method2ext'] = new ApiCall([$this, 'method2']); // add a method with a different name
        $methods['publicCall']->setPublic(); // make this one public
        return $methods;
    }

    function method1()
    {
        return null;
    }

    function methodString()
    {
        return 'success';
    }

    function method2($str, $int, $bool = false)
    {
        return array($str, $int, $bool);
    }

    function publicCall()
    {
        return true;
    }
}
