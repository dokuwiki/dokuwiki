#!/usr/bin/php -q
<?php
/**
* TODO: This needs migrating to inc/cli_opts.php
*/
ini_set('memory_limit','128M');

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');

require_once 'lib/testmanager.php';
TestManager::setup();

function usage() {
    $usage = <<<EOD
Usage: ./runtests.php [OPTION]...
Run the Dokuwiki unit tests remotely executing tests over HTTP and delivering
results to the command line. If ALL of the test cases pass a count of
total passes is printed on STDOUT. If ANY of the test cases fail (or raise
errors) details are printed on STDERR and this script returns a non-zero
exit code.
  -u  --url=HTTP_PATH     specify remote server test url (w. index.php)
  -f  --file=NAME         specify a test case file
  -g  --group=NAME        specify a grouptest. If no grouptest is
                          specified, all test cases will be run.
  -l  --glist             list available group tests
  -c  --clist             list available test case files
  -s, --separator=SEP     set the character(s) used to separate fail
                          details to SEP
  -p, --path              path to SimpleTest installation
  -h, --help              display this help and exit

EOD;
    echo $usage;
    exit(0);
}

/* default test options */
$opt_url = FALSE;
$opt_separator = '->';
$opt_group_list = FALSE;
$opt_case_list = FALSE;
$opt_casefile = FALSE;
$opt_groupfile = FALSE;

/* only allow cmd line options if PEAR Console_Getopt is available */
@include_once 'Console/Getopt.php'; /* PEAR lib */
if (class_exists('Console_Getopt')) {

    $argv = Console_Getopt::readPHPArgv();
    if (PEAR::isError($argv)) {
        die('Fatal Error: ' . $argv->getMessage()) . "\n";
    }

    $short_opts = "u:f:g:hlcs:p:";
    $long_opts  = array(
		"help", "url=", "file=", "group=",
		"glist", "clist", "separator=", "path="
		);
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
            case 'u':
            case '--url':
                $opt_url = $option[1];
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
            case '--glist':
                $opt_group_list = TRUE;
                break;
            case 'c':
            case '--clist':
                $opt_case_list = TRUE;
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


if ( !defined('SIMPLE_TEST') ) {
    define('SIMPLE_TEST', ConfigManager::getOptionAsPath('tests', 'simpletest', 'library_path'));
}
if (!@include_once SIMPLE_TEST . 'runner.php') {
    RaiseError('runtime', 'LIBRARY_REQUIRED', array(
        'library' => 'Simple Test',
        'path' => SIMPLE_TEST));
}
require_once 'lib/cli_reporter.php';

/* list tests */
if ($opt_group_list || $opt_case_list ) {

	if ($opt_group_list) {
		$gList = RemoteTestManager::getGroupTestList($opt_url);
	
		foreach ( $gList as $gName => $gUrl ) {
			fwrite(STDOUT,"[$gName] $gUrl\n");
		}
	}

	if ($opt_case_list) {
		$cList = RemoteTestManager::getTestCaseList($opt_url);
		
		foreach ( $cList as $cName => $cUrl ) {
			fwrite(STDOUT,"[$cName] $cUrl\n");
		}
	}
	
    exit(0);
}

/* run a test case */
if ($opt_casefile) {
    RemoteTestManager::runTestCase(
		$opt_casefile, new CLIReporter($opt_separator), $opt_url
		);
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