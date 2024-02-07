#Incutio XML-RPC library (IXR)

**Note**: _This is a fork of the original Incutio XML-RPC library (IXR) SVN repo hosted on <https://code.google.com/p/php-ixr/>_

All credits go to Incutio.

**Docs and Homepage:** <http://scripts.incutio.com/xmlrpc/>

#Introduction

The Incutio XML-RPC library (IXR) is designed primarily for ease of use. It incorporates both client and server classes, and is designed to hide as much of the workings of XML-RPC from the user as possible. A key feature of the library is automatic type conversion from PHP types to XML-RPC types and vice versa. This should enable developers to write web services with very little knowledge of the underlying XML-RPC standard.

Don't however be fooled by it's simple surface. The library includes a wide variety of additional XML-RPC specifications and has all of the features required for serious web service implementations.

#Background / History

The original XML-RPC library was developed back in 2002 and updated through 2010 by Incutio for a number of projects the company was working on at the time. It has become fairly dated but is still used extensively by a wide range of commercial and open-source projects.
This fork makes it usable on more recent systems (PHP 5.4+ ones)

#Composer

A [Composer](http://getcomposer.org/) file has been added to this repository.

This package is published to [Packagist](https://packagist.org/), but if you don't want to use it simply add

    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/kissifrot/php-ixr"
        }
    ],

    "require": {
        "kissifrot/php-ixr": "1.8.*"
    }

To your composer.json file
