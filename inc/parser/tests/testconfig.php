<?php
if (!defined('SIMPLE_TEST')) {
    // Should point at SimpleTest
    //(absolute path recommended with trailing slash)
    define('SIMPLE_TEST', '/var/www/simpletest/');
}

// Load SimpleTest
if ( @include_once SIMPLE_TEST . 'unit_tester.php' ) {
    require_once SIMPLE_TEST . 'mock_objects.php';
    require_once SIMPLE_TEST . 'reporter.php';
} else {
    trigger_error('Unable to load SimpleTest: '.
        'configure SIMPLE_TEST in config.php');
}

// Modify this as needed
define('DOKU_INC',realpath(dirname(__FILE__).'/../../../').'/');
?>
