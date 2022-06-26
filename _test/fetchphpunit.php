#!/usr/bin/env php
<?php
/**
 * Simple command line script to fetch the correct PHPUnit release
 */

$phpVersion = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
print "Running PHP $phpVersion\n";


if(version_compare($phpVersion, '7.2') < 0) {
    echo 'we no longer support PHP versions < 7.2 and thus do not support tests on them';
    exit(1);
}

// for now we default to phpunit-8
$phpunit = 'phpunit-8.phar';


$url = "https://phar.phpunit.de/$phpunit";
$out = __DIR__ . '/phpunit.phar';

$return = 0;
try {
    file_put_contents($out, file_get_contents($url));
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage());
    exit(1);
}

chmod($out, 0755);
print "Downloaded $phpunit\n";
exit(0);
