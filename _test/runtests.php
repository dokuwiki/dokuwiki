#!/usr/bin/php -q
<?php
ini_set('memory_limit','128M');
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');
define('DOKU_UNITTEST',true);

require_once(DOKU_INC.'inc/init.php');
require_once(DOKU_INC.'inc/events.php');

define('TEST_ROOT', dirname(__FILE__));
define('TMPL_FILESCHEME_PATH', TEST_ROOT . '/filescheme/');

require_once 'lib/testmanager.php';
TestManager::setup();

function usage() {
    $usage = <<<EOD
Usage: ./runtests.php [OPTION]...
Run the Dokuwiki unit tests. If ALL of the test cases pass a count of total
passes is printed on STDOUT. If ANY of the test cases fail (or raise
errors) details are printed on STDERR and this script returns a non-zero
exit code.
  -c  --case=NAME         specify a test case by it's ID (see -i for list)
  -f  --file=NAME         specify a test case file (full or relative path)
  -g  --group=NAME        specify a grouptest. If no grouptest is
                          specified, all test cases will be run.
  -i  --caselist          list individual test cases by their ID
  -l  --grouplist         list available grouptests
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
$opt_caselist = FALSE;
$opt_grouplist = FALSE;
$opt_caseid = FALSE;
$opt_casefile = FALSE;
$opt_groupfile = FALSE;

include_once(DOKU_INC.'inc/cliopts.php');

$short_opts = "c:f:g:hils:p:";
$long_opts  = array("case=","caselist","help", "file=", "group=", "grouplist", "separator=", "path=");
$OPTS = Doku_Cli_Opts::getOptions(__FILE__,$short_opts,$long_opts);
if ( $OPTS->isError() ) {
    fwrite( STDERR, $OPTS->getMessage() . "\n");
    usage($available_grouptests);
    exit(1);
}

foreach ($OPTS->options as $key => $val) {
    switch ($key) {
        case 'c':
        case 'case':
            $opt_caseid = $val;
            break;
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
        case 'i':
        case 'caselist':
            $opt_caselist = TRUE;
            break;
        case 'l':
        case 'grouplist':
            $opt_grouplist = TRUE;
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
if ($opt_grouplist) {
    echo CLITestManager::getGroupTestList(TEST_GROUPS);
}

/* list test cases */
if ($opt_caselist) {
    echo CLITestManager::getTestCaseList(TEST_CASES);
}

/* exit if we've displayed a list */
if ( $opt_grouplist || $opt_caselist ) {
    exit(0);
}

/* run a test case */
if ($opt_casefile) {
    TestManager::runTestFile($opt_casefile, new CLIReporter($opt_separator));
    exit(0);
}
/* run a test case by id*/
if ($opt_caseid) {
    TestManager::runTestCase($opt_caseid, TEST_CASES, new CLIReporter($opt_separator));
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
