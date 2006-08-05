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
		$dir = dirname(__FILE__).'/';		
        $this->GroupTest('ParserGroupTest');
        $this->addTestFile($dir . 'parser_eol.test.php');
        $this->addTestFile($dir . 'parser_footnote.test.php');
        $this->addTestFile($dir .'parser_formatting.test.php');
        $this->addTestFile($dir .'parser_headers.test.php');
        $this->addTestFile($dir .'parser_i18n.test.php');
        $this->addTestFile($dir .'parser_links.test.php');
        $this->addTestFile($dir .'parser_lists.test.php');
        $this->addTestFile($dir .'parser_preformatted.test.php');
        $this->addTestFile($dir .'parser_quote.test.php');
        $this->addTestFile($dir .'parser_replacements.test.php');
        $this->addTestFile($dir .'parser_table.test.php');
#        $this->addTestFile($dir .'parser_tocsections.test.php');
        $this->addTestFile($dir .'parser_unformatted.test.php');
    }
    
}

?>
