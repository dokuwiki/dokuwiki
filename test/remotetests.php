#!/usr/bin/php -q
<?php
ini_set('memory_limit','128M');

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');

require_once 'lib/testmanager.php';
TestManager::setup();

function usage() {
    $usage = <<<EOD
Usage: ./remotetests.php [OPTION]...
Run the Dokuwiki unit tests remotely executing tests over HTTP and delivering
results to the command line. If ALL of the test cases pass a count of
total passes is printed on STDOUT. If ANY of the test cases fail (or raise
errors) details are printed on STDERR and this script returns a non-zero
exit code.
  -c  --case=NAME         specify a test case by it's ID (see -i for list)
  -f  --caseurl=NAME         specify a test case file (full or relative path)
  -g  --group=NAME        specify a grouptest. If no grouptest is
                          specified, all test cases will be run.
  -i  --caselist          list individual test cases by their ID
  -l  --grouplist         list available grouptests
  -s, --separator=SEP     set the character(s) used to separate fail
                          details to SEP
  -p, --path              path to SimpleTest installation
  -h, --help              display this help and exit
  -u  --url=TEST_URL      specify remote server test url (w. index.php)

EOD;
    echo $usage;
    exit(0);
}

/* default test options */
$opt_separator = '->';
$opt_caselist = FALSE;
$opt_grouplist = FALSE;
$opt_caseid = FALSE;
$opt_caseurl = FALSE;
$opt_groupfile = FALSE;
$opt_url = FALSE;

include_once(DOKU_INC.'inc/cliopts.php');
$short_opts = "c:f:g:hils:p:u:";
$long_opts  = array("case=","caselist","help", "caseurl=", "group=", "grouplist", "separator=", "path=","url=");
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
        case 'caseurl':
            $opt_caseurl = $val;
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
        case 'u':
        case '--url':
            $opt_url = $val;
            break;
    }
}

if ( ! $opt_url ) {
    if ( !defined('REMOTE_TEST_URL') ) {
        fwrite( STDERR, "No test URL defined. Either modify tests.ini or use -u option\n");
        exit(1);
    } else {
        $opt_url = REMOTE_TEST_URL;
    }
}


if (!@include_once SIMPLE_TEST . 'reporter.php') {
    if ( defined(SIMPLE_TEST) ) {
        fwrite( STDERR, "Where's Simple Test ?!? Not at ".SIMPLE_TEST." \n");
    } else {
        fwrite( STDERR, "Where's Simple Test ?!? SIMPLE_TEST not even defined!\n");
    }
    exit(1);
}

require_once 'lib/cli_reporter.php';

/* list grouptests */
if ($opt_grouplist) {
    $groups = RemoteTestManager::getGroupTestList($opt_url);
    fwrite( STDOUT, "Available grouptests:\n");
    foreach ( array_keys($groups) as $group ) {
        fwrite( STDOUT, $group."\n");
    }
}

/* list test cases */
if ($opt_caselist) {
    $cases = RemoteTestManager::getTestCaseList($opt_url);
    fwrite( STDOUT, "Available tests tests:\n");
    foreach ( array_keys($cases) as $case ) {
        fwrite( STDOUT, $case."\n");
    }
}

/* exit if we've displayed a list */
if ( $opt_grouplist || $opt_caselist ) {
    exit(0);
}

/* run a test case given it's URL */
if ($opt_caseurl) {
    RemoteTestManager::runTestUrl($opt_caseurl, new CLIReporter($opt_separator), $opt_url);
    exit(0);
}

/* run a test case by id*/
if ($opt_caseid) {
    RemoteTestManager::runTestCase($opt_caseid, new CLIReporter($opt_separator), $opt_url);
    exit(0);
}

/* run a grouptest */
if ($opt_groupfile) {
    RemoteTestManager::runGroupTest(
        $opt_groupfile, new CLIReporter($opt_separator), $opt_url
        );
    exit(0);
}
/* run all tests */
RemoteTestManager::runAllTests(new CLIReporter($opt_separator), $opt_url);
exit(0);
?>