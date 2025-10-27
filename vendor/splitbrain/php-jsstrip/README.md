# PHP JSStrip

This is a PHP port of Nick Galbreath's python tool [jsstrip.py](https://code.google.com/p/jsstrip/).

It was originally ported to PHP in 2006 as part of the [DokuWiki](http://www.dokuwiki.org) wiki engine. It has received several improvements over the years and is now available as a standalone library.

Quoting the original description:

jsstrip is a open-source library to remove whitespace and comments from a javascript file. You might want to do this to optimize size and performance, or to make a file harder to read. It typically makes 30-40% savings in file size.

**WARNING**

jsstrip is not a true javascript parser. It assumes you have properly delimited the 'end of line' using a ';' (semicolon).

  * Yes `print 'foo'; print 'bar';`
  * No `print 'foo' print 'bar'`

You'll have to convert your code to use ';' first.

ALWAYS test the stripped version before deploying to production.


## Installation

Install via composer

    composer require splitbrain/php-jsstrip

## Usage

```php
<?php

require_once 'vendor/autoload.php';

$js = file_get_contents('somefile.js'); // gather your JS here somehow

$minifiedJS = (new \splitbrain\JSStrip\JSStrip())->compress($js);
```

## Skip Minification

You can skip Minification for parts of your code by surrounding it by special comments:

```js
/* BEGIN NOCOMPRESS */
const foo = 'No compression here'; // this comment will also stay
/* END NOCOMPRESS */
```
