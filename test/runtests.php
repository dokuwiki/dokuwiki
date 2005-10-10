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
include_once(DOKU_INC.'inc/cliopts.php');

$short_opts = "f:g:hls:p:";
$long_opts  = array("help", "file=", "group=", "list", "separator=", "path=");
$OPTS = Doku_Cli_Opts::getOptions(__FILE__,$short_opts,$long_opts);
if ( $OPTS->isError() ) {
    fwrite( STDERR, $OPTS->getMessage() . "\n");
    usage($available_grouptests);
    exit(1);
}

foreach ($OPTS->options as $key => $val) {
    switch ($key) {
        case 'h':
        case 'help':
            usage();
            break;
        case 'f':
        case 'file':
            $opt_casefile = $val;
            break;
        case 'g':
        case 'group':
            $opt_groupfile = $val;
            break;
        case 'l':
        case 'list':
            $opt_list = TRUE;
            break;
        case 's':
        case 'separator':
            $opt_separator = $val;
            break;
        case 'p':
        case 'path':
            if (file_exists($val)) {
                define('SIMPLE_TEST', $val);
            }
            break;
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
