<?php

namespace dokuwiki\test\Remote\Mock;


use dokuwiki\Remote\ApiCall;
use dokuwiki\Remote\Response\Link;

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
            'wiki.getStructuredData' => (new ApiCall([$this, 'getStructuredData'])),
        ];
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

    function getStructuredData()
    {
        return new Link('internal', 'wiki:dokuwiki', 'https://www.dokuwiki.org/wiki:dokuwiki');
    }
}
