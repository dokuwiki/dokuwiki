<?php
/**
* @version $Id: parser.group.php,v 1.3 2005/03/30 13:42:10 harryf Exp $
* @package Dokuwiki
* @subpackage Tests
*/

/**
* @package Dokuwiki
* @subpackage Tests
*/
class ParserGroupTest extends GroupTest {

    function ParserGroupTest() {
        $this->GroupTest('ParserGroupTest');
        $this->addTestFile('parser_eol.test.php');
        $this->addTestFile('parser_footnote.test.php');
        $this->addTestFile('parser_formatting.test.php');
        $this->addTestFile('parser_headers.test.php');
        $this->addTestFile('parser_i18n.test.php');
        $this->addTestFile('parser_links.test.php');
        $this->addTestFile('parser_lists.test.php');
        $this->addTestFile('parser_preformatted.test.php');
        $this->addTestFile('parser_quote.test.php');
        $this->addTestFile('parser_replacements.test.php');
        $this->addTestFile('parser_table.test.php');
        $this->addTestFile('parser_tocsections.test.php');
        $this->addTestFile('parser_unformatted.test.php');
    }
    
}

/**
* Conditional test runner
*/
if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new ParserGroupTest();
    $test->run(new HtmlReporter());
}
?>
