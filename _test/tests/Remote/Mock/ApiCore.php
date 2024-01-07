<?php

namespace dokuwiki\test\Remote\Mock;


use dokuwiki\Remote\ApiCall;

class ApiCore
{

    function getMethods()
    {
        return [
            'wiki.stringTestMethod' => new ApiCall([$this, 'stringTestMethod']),
            'wiki.intTestMethod' => new ApiCall([$this, 'intTestMethod']),
            'wiki.floatTestMethod' => new ApiCall([$this, 'floatTestMethod']),
            'wiki.dateTestMethod' => new ApiCall([$this, 'dateTestMethod']),
            'wiki.fileTestMethod' => new ApiCall([$this, 'fileTestMethod']),
            'wiki.voidTestMethod' => new ApiCall([$this, 'voidTestMethod']),
            'wiki.oneStringArgMethod' => new ApiCall([$this, 'oneStringArgMethod']),
            'wiki.twoArgMethod' => new ApiCall([$this, 'twoArgMethod']),
            'wiki.twoArgWithDefaultArg' => new ApiCall([$this, 'twoArgWithDefaultArg']),
            'wiki.publicCall' => (new ApiCall([$this, 'publicCall']))->setPublic(),
        ];

        /*
           array(
           'wiki.stringTestMethod' => array(
               'args' => array(),
               'return' => 'string',
               'doc' => 'Test method',
               'name' => 'stringTestMethod',
           ), 'wiki.intTestMethod' => array(
               'args' => array(),
               'return' => 'int',
               'doc' => 'Test method',
               'name' => 'intTestMethod',
           ), 'wiki.floatTestMethod' => array(
               'args' => array(),
               'return' => 'float',
               'doc' => 'Test method',
               'name' => 'floatTestMethod',
           ), 'wiki.dateTestMethod' => array(
               'args' => array(),
               'return' => 'date',
               'doc' => 'Test method',
               'name' => 'dateTestMethod',
           ), 'wiki.fileTestMethod' => array(
               'args' => array(),
               'return' => 'file',
               'doc' => 'Test method',
               'name' => 'fileTestMethod',
           ), 'wiki.voidTestMethod' => array(
               'args' => array(),
               'return' => 'void',
               'doc' => 'Test method',
               'name' => 'voidTestMethod',
           ),  'wiki.oneStringArgMethod' => array(
               'args' => array('string'),
               'return' => 'string',
               'doc' => 'Test method',
               'name' => 'oneStringArgMethod',
           ), 'wiki.twoArgMethod' => array(
               'args' => array('string', 'int'),
               'return' => 'array',
               'doc' => 'Test method',
               'name' => 'twoArgMethod',
           ), 'wiki.twoArgWithDefaultArg' => array(
               'args' => array('string', 'string'),
               'return' => 'string',
               'doc' => 'Test method',
               'name' => 'twoArgWithDefaultArg',
           ), 'wiki.publicCall' => array(
               'args' => array(),
               'return' => 'boolean',
               'doc' => 'testing for public access',
               'name' => 'publicCall',
               'public' => 1
           )
       );
           */
    }

    function stringTestMethod()
    {
        return 'success';
    }

    function intTestMethod()
    {
        return 42;
    }

    function floatTestMethod()
    {
        return 3.14159265;
    }

    function dateTestMethod()
    {
        return 2623452346;
    }

    function fileTestMethod()
    {
        return 'file content';
    }

    function voidTestMethod()
    {
        return null;
    }

    function oneStringArgMethod($arg)
    {
        return $arg;
    }

    function twoArgMethod($string, $int)
    {
        return array($string, $int);
    }

    function twoArgWithDefaultArg($string1, $string2 = 'default')
    {
        return array($string1, $string2);
    }

    function publicCall()
    {
        return true;
    }

}
