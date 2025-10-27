# LesserPHP (reloaded)

LesserPHP is a compiler for LESS written in PHP. It is based on `lessphp` by [@leafo](https://github.com/leafo/lessphp). The original has been abandoned in 2014. The fork by [@MarcusSchwarz](https://github.com/MarcusSchwarz/lesserphp) has been mostly abandoned in 2021. There are other forks with dubious status.

This is an opinionated fork with the goal to modernize the code base enough to be somewhat easier to maintain without completely rewriting it. It is meant to be used as a stable base for [DokuWiki](https://www.dokuwiki.org). This means features not needed for this goal are removed. 

Please note that this fork is based on the `0.6.0-dev` branch of `MarcusSchwarz/lesserphp`, not the much modernized `master` branch. This has two reasons:

1. The `master` was not up-to-date with all the bug fixes in the `0.6.0-dev` branch (some of which had been contributed by DokuWiki developers)
2. I simply only noticed the considerable refactoring Marcus had done in the `master` branch after I had already started my own refactoring. I did not want to start over again. His approach is much more radical than mine and probably took more than the long weekend I had available for this.  

## Contributing and Bugs

Please report bugs to the [issue tracker](https://github.com/splitbrain/lesserphp/issues). Fixes are only likely when DokuWiki needs them, or you provide a pull request.

Feature Requests will be ignored unless accompanied by a pull request.

## How to use in your PHP project

Don't. You really wouldn't want to start a new project using LESS. It simply seems that SASS has won the battle. Or maybe even skip the whole CSS preprocessor thing - modern CSS is quite powerful on its own. 

If you are already using `lessphp` in one of it's many forks, using this one isn't too different.

You can still look at the [original documentation](https://leafo.net/lessphp/docs/) for the most part. The API is mostly the same. Refer to the [upstream documentation](https://lesscss.org/features/) the [bundled Documentation](docs/docs.md) for the LESS syntax itself. Keep in mind that some more modern features are not supported by LesserPHP.

To install it, use composer:

```bash
composer require splitbrain/lesserphp
```

The typical flow of LesserPHP is to create a new instance of `Lessc`,
configure it how you like, then tell it to compile something using one built in
compile methods.

The `compile` method compiles a string of LESS code to CSS.

```php
<?php
require __DIR__ . '/vendor/autoload.php';

$less = new LesserPHP\Lessc;
echo $less->compile(".block { padding: 3 + 4px }");
```

The `compileFile` method reads and compiles a file. It will either return the
result or write it to the path specified by an optional second argument.

```php
<?php
echo $less->compileFile("input.less");
```


If there's any problem compiling your code, an exception is thrown with a helpful message:

```php
<?php
try {
  $less->compile("invalid LESS } {");
} catch (LesserPHP\ParserException $e) {
  echo $e->getMessage();
}
```
