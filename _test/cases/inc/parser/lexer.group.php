<?php
/**
* @version $Id: lexer.group.php,v 1.2 2005/03/25 21:00:22 harryf Exp $
* @package JPSpan
* @subpackage Tests
*/

/**
* Init
*/
require_once('./testconfig.php');

/**
* @package JPSpan
* @subpackage Tests
*/
class LexerGroupTest extends GroupTest {

    function LexerGroupTest() {
        $this->GroupTest('LexerGroupTest');
        $this->addTestFile('lexer.test.php');
    }
    
}

/**
* Conditional test runner
*/
if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new LexerGroupTest();
    $test->run(new HtmlReporter());
}
?>
