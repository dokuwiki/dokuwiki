<?php
/**
* @version $Id: lexer.group.php,v 1.2 2005/03/25 21:00:22 harryf Exp $
* @package JPSpan
* @subpackage Tests
*/

/**
* @package JPSpan
* @subpackage Tests
*/
class LexerGroupTest extends GroupTest {

    function LexerGroupTest() {
        $this->GroupTest('LexerGroupTest');
        $this->addTestFile(dirname(__FILE__).'/lexer.test.php');
    }
    
}

?>
