<?php // -*- fill-column: 80; tab-width: 4; c-basic-offset: 4 -*-
/**
* Lots TODO here...
*/

define('TEST_GROUPS',realpath(dirname(__FILE__).'/../cases'));
define('TEST_CASES',realpath(dirname(__FILE__).'/../cases'));

// try to load runkit extension
if (!extension_loaded('runkit') && function_exists('dl')) {
   if (strtoupper(substr(PHP_OS, 0, 3) == 'WIN')) {
       @dl('php_runkit.dll');
   } else {
       @dl('runkit.so');
   }
}

class TestManager {
    var $_testcase_extension = '.test.php';
    var $_grouptest_extension = '.group.php';
    
    function setup() {
        $ini_file = realpath(dirname(__FILE__).'/../tests.ini');
	
        if (! file_exists($ini_file)) {
            trigger_error("Missing configuration file {$ini_file}",
                          E_USER_ERROR);
        }
        $config = parse_ini_file($ini_file);
        foreach ($config as $key => $value) {
            define($key, $value);
        }
        TestManager::_installSimpleTest();

				list($version) = file(SIMPLE_TEST.'VERSION');
				$version = trim($version);
				if(!version_compare('1.0.1alpha',$version,'<')){
						echo "At least SimpleTest Version 1.0.1alpha is required.";
						echo " Yours is $version\n";
						exit;
				}
    }
    
    function _installSimpleTest() {
        require_once SIMPLE_TEST . 'unit_tester.php';
        require_once SIMPLE_TEST . 'web_tester.php';
        require_once SIMPLE_TEST . 'mock_objects.php';
        require_once 'web.inc.php';
        require_once 'mock_functions.php';
    }

    function runAllTests(&$reporter) {
        $manager =& new TestManager();
        $test_cases =& $manager->_getTestFileList();
        $test =& new GroupTest('All Tests');
        foreach ($test_cases as $test_case) {
            $test->addTestFile($test_case);
        }
        $test->run($reporter);
    }

    function runTestCase($testcase_name, $test_case_directory, &$reporter) {
        $manager =& new TestManager();
        
        $testcase_name = preg_replace('/[^a-zA-Z0-9_:]/','',$testcase_name);
        $testcase_name = str_replace(':',DIRECTORY_SEPARATOR,$testcase_name);
        
        $testcase_file = $test_case_directory . DIRECTORY_SEPARATOR .
            strtolower($testcase_name) . $manager->_testcase_extension;
        
        if (! file_exists($testcase_file)) {
            trigger_error("Test case {$testcase_file} cannot be found",
                          E_USER_ERROR);
        }

        $test =& new GroupTest("Individual test case: " . $testcase_name);
        $test->addTestFile($testcase_file);
        $test->run($reporter);
    }
    
    function runTestFile($testcase_file, &$reporter) {
        $manager =& new TestManager();
        
        if (! file_exists($testcase_file)) {
            trigger_error("Test case {$testcase_file} cannot be found",
                          E_USER_ERROR);
        }

        $test =& new GroupTest("Individual test case: " . $testcase_file);
        $test->addTestFile($testcase_file);
        $test->run($reporter);
    }

    function runGroupTest($group_test_name, $group_test_directory, &$reporter) {
        $manager =& new TestManager();
        $group_test_name = preg_replace('/[^a-zA-Z0-9_:]/','',$group_test_name);
        $group_test_name = str_replace(':',DIRECTORY_SEPARATOR,$group_test_name);
        $file_path = $group_test_directory . DIRECTORY_SEPARATOR .
            strtolower($group_test_name) . $manager->_grouptest_extension;

        if (! file_exists($file_path)) {
            trigger_error("Group test {$group_test_name} cannot be found at {$file_path}",
                          E_USER_ERROR);
        }

        require_once $file_path;
        $test =& new GroupTest($group_test_name . ' group test');
        foreach ($manager->_getGroupTestClassNames($file_path) as $group_test) {
            $test->addTestCase(new $group_test());
        }
        $test->run($reporter);
    }

    function addTestCasesFromDirectory(&$group_test, $directory = '.') {
        $manager =& new TestManager();
        $test_cases =& $manager->_getTestFileList($directory);
        foreach ($test_cases as $test_case) {
            $group_test->addTestFile($test_case);
        }
    }

    function &getTestCaseList($directory = '.') {
        $manager =& new TestManager();
        return $manager->_getTestCaseList($directory);
    }

    function &_getTestCaseList($directory = '.') {
        $base = TEST_GROUPS . DIRECTORY_SEPARATOR;
        $file_list =& $this->_getTestFileList($directory);
        $testcases = array();
        foreach ($file_list as $testcase_file) {
            $case = str_replace($this->_testcase_extension, '',$testcase_file);
            $case = str_replace($base, '', $case);
            $case = str_replace(DIRECTORY_SEPARATOR, ':', $case);
            $testcases[$testcase_file] = $case;
        }
        return $testcases;
    }

    function &_getTestFileList($directory = '.') {
        return $this->_getRecursiveFileList($directory,
                                            array(&$this, '_isTestCaseFile'));
    }

    function &getGroupTestList($directory = '.') {
        $manager =& new TestManager();
        return $manager->_getTestGroupList($directory);
    }

    function &_getTestGroupFileList($directory = '.') {
        return $this->_getRecursiveFileList($directory,
                                            array(&$this, '_isTestGroupFile'));
    }

    function &_getTestGroupList($directory = '.') {
        $base = TEST_GROUPS . DIRECTORY_SEPARATOR;
        $file_list =& $this->_getTestGroupFileList($directory);
        $grouptests = array();
        foreach ($file_list as $grouptest_file) {
            $group = str_replace($this->_grouptest_extension, '',$grouptest_file);
            $group = str_replace($base, '', $group);
            $group = str_replace(DIRECTORY_SEPARATOR, ':', $group);
            $grouptests[$grouptest_file] = $group;
        }
        sort($grouptests);
        return $grouptests;
    }

    function &_getGroupTestClassNames($grouptest_file) {
        $file = implode("\n", file($grouptest_file));
        preg_match("~lass\s+?(.*)\s+?extends GroupTest~", $file, $matches);
        if (! empty($matches)) {
            unset($matches[0]);
            return $matches;
        } else {
            return array();
        }
    }

    function &_getRecursiveFileList($directory = '.', $file_test_function) {
        $dh = opendir($directory);
        if (! is_resource($dh)) {
            trigger_error("Couldn't open {$directory}", E_USER_ERROR);
        }

        $file_list = array();
        while ($file = readdir($dh)) {
            $file_path = $directory . DIRECTORY_SEPARATOR . $file;

            if (0 === strpos($file, '.')) continue;

            if (is_dir($file_path)) {
                $file_list =
                    array_merge($file_list,
                                $this->_getRecursiveFileList($file_path,
                                                             $file_test_function));
            }
            if ($file_test_function[0]->$file_test_function[1]($file)) {
                $file_list[] = $file_path;
            }
        }
        closedir($dh);
        return $file_list;
    }

    function _isTestCaseFile($file) {
        return $this->_hasExpectedExtension($file, $this->_testcase_extension);
    }

    function _isTestGroupFile($file) {
        return $this->_hasExpectedExtension($file, $this->_grouptest_extension);
    }

    function _hasExpectedExtension($file, $extension) {
        return $extension ==
            strtolower(substr($file, (0 - strlen($extension))));
    }
}

/**
* @package WACT_TESTS
*/
class CLITestManager extends TestManager {
    function &getGroupTestList($directory = '.') {
        $manager =& new CLITestManager();
        $group_tests =& $manager->_getTestGroupList($directory);

        $buffer = "Available grouptests:\n";
        foreach ($group_tests as $group_test) {
            $buffer .= "  " . $group_test . "\n";
        }
        return $buffer . "\n";
    }

    function &getTestCaseList($directory = '.') {
        $manager =& new CLITestManager();
        $test_cases =& $manager->_getTestCaseList($directory);

        $buffer = "Available test cases:\n";
        foreach ($test_cases as $test_case) {
            $buffer .= "  " . $test_case . "\n";
        }
        return $buffer . "\n";
    }
}

class HTMLTestManager extends TestManager {
    var $_url;

    function HTMLTestManager() {
        $this->_url = $_SERVER['PHP_SELF'];
    }

    function getBaseURL() {
        return $this->_url;
    }

    function &getGroupTestList($directory = '.') {
        $manager =& new HTMLTestManager();
        $group_tests =& $manager->_getTestGroupList($directory);
        if (1 > count($group_tests)) {
            return "<p>No test groups set up!</p>";
        }
        $buffer = "<p>Available test groups:</p>\n<ul>";
        $buffer .= "<li><a href='" . $manager->getBaseURL() . "?group=all'>All tests</a></li>\n";
        foreach ($group_tests as $group_test) {
            $buffer .= "<li><a href='" . $manager->getBaseURL() . "?group={$group_test}'>" .
                $group_test . "</a></li>\n";
        }

        $buffer .= "</ul>\n";
        return $buffer;
    }

    function &getTestCaseList($directory = '.') {
        $manager =& new HTMLTestManager();
        $testcases =& $manager->_getTestCaseList($directory);

        if (1 > count($testcases)) {
            return "<p>No test cases set up!</p>";
        }
        $buffer = "<p>Available test cases:</p>\n<ul>";
        foreach ($testcases as $testcase) {
            $buffer .= "<li><a href='" . $manager->getBaseURL() .
                "?case=" . urlencode($testcase) . "'>" .
                $testcase . "</a></li>\n";
        }

        $buffer .= "</ul>\n";
        return $buffer;
    }
}

/**
* @package WACT_TESTS
*/
class XMLTestManager extends HTMLTestManager {

    function XMLTestManager() {
        parent::HTMLTestManager();
    }

    function &getGroupTestList($directory = '.') {

        $manager =& new XMLTestManager();
        $group_tests =& $manager->_getTestGroupList($directory);

        $rss = & $manager->_getRssWriter();

        if (1 > count($group_tests)) {
            $rss->writeRss($output);
            return $output;
        }

        $properties["title"]="All Tests";
        $properties["description"]="All Tests";
        $properties["link"]='http://'.$_SERVER['SERVER_NAME'].
            $manager->getBaseURL()."?group=all&output=xml";

        $rss->additem($properties);

        foreach ($group_tests as $group_test) {
            $properties["title"]=$group_test;
            $properties["description"]=$group_test;
            $properties["link"]='http://'.$_SERVER['SERVER_NAME'].
                $manager->getBaseURL().
                    "?group={$group_test}&output=xml";

            $rss->additem($properties);
        }
        if ( !$rss->writeRss($output) ) {
            die ( $rss->error );
        }
        return $output;

    }

    function &getTestCaseList($directory = '.') {

        $manager =& new XMLTestManager();
        $testcases =& $manager->_getTestCaseList($directory);

        $rss = & $manager->_getRssWriter();

        if (1 > count($testcases)) {
            $rss->writeRss($output);
            return $output;
        }

        foreach ($testcases as $testfile => $testcase) {
            $properties["title"]=$testcase;
            $properties["description"]=$testcase;
            $properties["link"]='http://'.$_SERVER['SERVER_NAME'].
                $manager->getBaseURL()."?case=" .
                    urlencode($testcase) . "&output=xml";

            // Comment this out for performance?
            $properties["dc:date"]=gmdate("Y-m-d\TH:i:sO",filemtime($testfile));

            $rss->additem($properties);
        }

        $rss->writeRss($output);
        return $output;
    }

    function &_getRssWriter() {

        $url = 'http://'.$_SERVER['SERVER_NAME'].str_replace('index.php','',$_SERVER['PHP_SELF']);

        require_once TEST_ROOT . '/lib/xml_writer_class.php';
        require_once TEST_ROOT . '/lib/rss_writer_class.php';

        $rss_writer_object=& new rss_writer_class();
        $rss_writer_object->specification="1.0";
        $rss_writer_object->about=$url."index.php?output=xml";
        $rss_writer_object->stylesheet=$url."rss2html.xsl";
        $rss_writer_object->rssnamespaces["dc"]="http://purl.org/dc/elements/1.1/";

        // Channel Properties
        $properties=array();
        $properties["title"]="Dokuwiki Unit Test Cases";
        $properties["description"]="Dokuwiki Unit Test Cases";
        $properties["link"]="http://wiki.splitbrain.org/";
        $properties["dc:date"]=gmdate("Y-m-d\TH:i:sO");
        $rss_writer_object->addchannel($properties);

        // Logo like this (if we had one)
        /*
        $properties=array();
        
        $properties["link"]="http://www.phpclasses.org/";
        $properties["title"]="PHP Classes repository logo";
        $properties["description"]="Repository of components and other resources for PHP developers";
        $rss_writer_object->addimage($properties);
        */

        return $rss_writer_object;
    }

}

/**
* @package WACT_TESTS
*/
class RemoteTestManager extends TestManager {

    function RemoteTestManager() {
        RemoteTestManager::_installSimpleTest();
    }

    function _installSimpleTest() {
        require_once SIMPLE_TEST . 'remote.php';
    }

    function runAllTests(&$reporter, $url = FALSE) {
        $groups = RemoteTestManager::getGroupTestList($url);
        $T = &new RemoteTestCase($groups['All Tests']);
        $T->run($reporter);
    }
    
    function runTestUrl($case_url,& $reporter, $url = FALSE) {
        RemoteTestManager::_installSimpleTest();
        $T = &new RemoteTestCase($case_url);
        $T->run($reporter);
    }

    function runTestCase($case_id,& $reporter, $url = FALSE) {
        $cases = RemoteTestManager::getTestCaseList($url);
        if ( !array_key_exists($case_id, $cases) ) {
            trigger_error("Unknown test id $case_id\n",E_USER_ERROR);
        }
        $T = &new RemoteTestCase($cases[$case_id]);
        $T->run($reporter);
    }

    function runGroupTest($group_name, &$reporter, $url = FALSE) {
        $groups = RemoteTestManager::getGroupTestList($url);
        if ( !array_key_exists($group_name, $groups) ) {
            trigger_error("Unknown group $group_name\n",E_USER_ERROR);
        }
        $T = &new RemoteTestCase($groups[$group_name]);
        $T->run($reporter);
    }

    function & getGroupTestList($url = FALSE) {

        if ( !$url ) {
            $url = REMOTE_TEST_URL;
        }

        $url .= '?output=xml';
        
        $manager =& new RemoteTestManager();
        $rss = & $manager->_getRssReader($url);

        $groupList = array();

        foreach ($rss->getItems() as $item) {
            $groupList[$item['title']] = $item['link'];
        }

        return $groupList;
    }

    function &getTestCaseList($url = FALSE) {
        if ( !$url ) {
            $url = REMOTE_TEST_URL;
        }

        $url .= '?show=cases&output=xml';
        $manager =& new RemoteTestManager();
        $rss = & $manager->_getRssReader($url);

        $caseList = array();

        foreach ($rss->getItems() as $item) {
            $caseList[$item['title']] = $item['link'];
        }

        return $caseList;
    }

    function &_getRssReader($url) {
        require_once "XML/RSS.php";

        $rss_reader =& new XML_RSS($url);

        $status = $rss_reader->parse();

        if (PEAR::isError($status) ) {
            trigger_error($status->getMessage(),E_USER_WARNING);
        }

        return $rss_reader;
    }

}
