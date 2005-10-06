#!/usr/bin/php -q
<?php
/**
* TODO: This needs migrating to inc/cli_opts.php
*/

ini_set('memory_limit','128M');
/* wact common */
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');
define('TEST_ROOT', dirname(__FILE__));
define('TMPL_FILESCHEME_PATH', TEST_ROOT . '/filescheme/');
error_reporting(E_ALL);

require_once 'lib/testmanager.php';
TestManager::setup();

function usage() {
    $usage = <<<EOD
Usage: ./runtests.php [OPTION]...
Run the Dokuwiki unit tests. If ALL of the test cases pass a count of total
passes is printed on STDOUT. If ANY of the test cases fail (or raise
errors) details are printed on STDERR and this script returns a non-zero
exit code.
  -f  --file=NAME         specify a test case file
  -g  --group=NAME        specify a grouptest. If no grouptest is
                          specified, all test cases will be run.
  -l  --list              list available grouptests/test case files
  -s, --separator=SEP     set the character(s) used to separate fail
                          details to SEP
  -p, --path              path to SimpleTest installation
  -h, --help              display this help and exit

EOD;
    echo $usage;
    exit(0);
}

/* test options */
$opt_separator = '->';
$opt_list = FALSE;
$opt_casefile = FALSE;
$opt_groupfile = FALSE;

/* only allow cmd line options if PEAR Console_Getopt is available */
@include_once 'Console/Getopt.php'; /* PEAR lib */
if (class_exists('Console_Getopt')) {

    $argv = Console_Getopt::readPHPArgv();
    if (PEAR::isError($argv)) {
        die('Fatal Error: ' . $argv->getMessage()) . "\n";
    }

    $short_opts = "f:g:hls:p:";
    $long_opts  = array("help", "file=", "group=", "list", "separator=", "path=");
    $options = Console_Getopt::getopt($argv, $short_opts, $long_opts);
    if (PEAR::isError($options)) {
        usage($available_grouptests);
    }

    foreach ($options[0] as $option) {
        switch ($option[0]) {
            case 'h':
            case '--help':
                usage();
                break;
            case 'f':
            case '--file':
                $opt_casefile = $option[1];
                break;
            case 'g':
            case '--group':
                $opt_groupfile = $option[1];
                break;
            case 'l':
            case '--list':
                $opt_list = TRUE;
                break;
            case 's':
            case '--separator':
                $opt_separator = $option[1];
                break;
            case 'p':
            case '--path':
                if (file_exists($option[1])) {
                    define('SIMPLE_TEST', $option[1]);
                }
                break;
        }
    }
}

if (!@include_once SIMPLE_TEST . 'reporter.php') {
    die("Where's Simple Test ?!? Not at ".SIMPLE_TEST);
}

require_once 'lib/cli_reporter.php';

/* list grouptests */
if ($opt_list) {
    echo CLITestManager::getGroupTestList(TEST_GROUPS);
    echo CLITestManager::getTestCaseList(TEST_CASES);
    exit(0);
}
/* run a test case */
if ($opt_casefile) {
    TestManager::runTestCase($opt_casefile, new CLIReporter($opt_separator));
    exit(0);
}
/* run a grouptest */
if ($opt_groupfile) {
    TestManager::runGroupTest($opt_groupfile, TEST_GROUPS,
                              new CLIReporter($opt_separator));
    exit(0);
}
/* run all tests */
TestManager::runAllTests(new CLIReporter($opt_separator));
exit(0);
?>
