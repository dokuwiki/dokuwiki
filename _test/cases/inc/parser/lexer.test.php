<?php
/**
* @version $Id: lexer.test.php,v 1.2 2005/03/25 21:00:22 harryf Exp $
* @package Doku
* @subpackage Tests
*/

/**
* Includes
*/
require_once DOKU_INC . 'inc/parser/lexer.php';
    
/**
* @package Doku
* @subpackage Tests
*/
class TestOfLexerParallelRegex extends UnitTestCase {
	function TestOfLexerParallelRegex() {
		$this->UnitTestCase();
	}
	function testNoPatterns() {
		$regex = new Doku_LexerParallelRegex(false);
		$this->assertFalse($regex->match("Hello", $match));
		$this->assertEqual($match, "");
	}
	function testNoSubject() {
		$regex = new Doku_LexerParallelRegex(false);
		$regex->addPattern(".*");
		$this->assertTrue($regex->match("", $match));
		$this->assertEqual($match, "");
	}
	function testMatchAll() {
		$regex = new Doku_LexerParallelRegex(false);
		$regex->addPattern(".*");
		$this->assertTrue($regex->match("Hello", $match));
		$this->assertEqual($match, "Hello");
	}
	function testCaseSensitive() {
		$regex = new Doku_LexerParallelRegex(true);
		$regex->addPattern("abc");
		$this->assertTrue($regex->match("abcdef", $match));
		$this->assertEqual($match, "abc");
		$this->assertTrue($regex->match("AAABCabcdef", $match));
		$this->assertEqual($match, "abc");
	}
	function testCaseInsensitive() {
		$regex = new Doku_LexerParallelRegex(false);
		$regex->addPattern("abc");
		$this->assertTrue($regex->match("abcdef", $match));
		$this->assertEqual($match, "abc");
		$this->assertTrue($regex->match("AAABCabcdef", $match));
		$this->assertEqual($match, "ABC");
	}
	function testMatchMultiple() {
		$regex = new Doku_LexerParallelRegex(true);
		$regex->addPattern("abc");
		$regex->addPattern("ABC");
		$this->assertTrue($regex->match("abcdef", $match));
		$this->assertEqual($match, "abc");
		$this->assertTrue($regex->match("AAABCabcdef", $match));
		$this->assertEqual($match, "ABC");
		$this->assertFalse($regex->match("Hello", $match));
	}
	function testPatternLabels() {
		$regex = new Doku_LexerParallelRegex(false);
		$regex->addPattern("abc", "letter");
		$regex->addPattern("123", "number");
		$this->assertIdentical($regex->match("abcdef", $match), "letter");
		$this->assertEqual($match, "abc");
		$this->assertIdentical($regex->match("0123456789", $match), "number");
		$this->assertEqual($match, "123");
	}
	function testMatchMultipleWithLookaheadNot() {
		$regex = new Doku_LexerParallelRegex(true);
		$regex->addPattern("abc");
		$regex->addPattern("ABC");
		$regex->addPattern("a(?!\n).{1}");
		$this->assertTrue($regex->match("abcdef", $match));
		$this->assertEqual($match, "abc");
		$this->assertTrue($regex->match("AAABCabcdef", $match));
		$this->assertEqual($match, "ABC");
		$this->assertTrue($regex->match("a\nab", $match));
		$this->assertEqual($match, "ab");
		$this->assertFalse($regex->match("Hello", $match));
	}
	function testMatchSetOptionCaseless() {
		$regex = new Doku_LexerParallelRegex(true);
		$regex->addPattern("a(?i)b(?i)c");
		$this->assertTrue($regex->match("aBc", $match));
		$this->assertEqual($match, "aBc");
	}
	function testMatchSetOptionUngreedy() {
		$regex = new Doku_LexerParallelRegex(true);
		$regex->addPattern("(?U)\w+");
		$this->assertTrue($regex->match("aaaaaa", $match));
		$this->assertEqual($match, "a");
	}
	function testMatchLookaheadEqual() {
		$regex = new Doku_LexerParallelRegex(true);
		$regex->addPattern("\w(?=c)");
		$this->assertTrue($regex->match("xbyczd", $match));
		$this->assertEqual($match, "y");
	}
	function testMatchLookaheadNot() {
		$regex = new Doku_LexerParallelRegex(true);
		$regex->addPattern("\w(?!b|c)");
		$this->assertTrue($regex->match("xbyczd", $match));
		$this->assertEqual($match, "b");
	}
	function testMatchLookbehindEqual() {
		$regex = new Doku_LexerParallelRegex(true);
		$regex->addPattern("(?<=c)\w");
		$this->assertTrue($regex->match("xbyczd", $match));
		$this->assertEqual($match, "z");
	}
	function testMatchLookbehindNot() {
		$regex = new Doku_LexerParallelRegex(true);
		$regex->addPattern("(?<!\A|x|b)\w");
		$this->assertTrue($regex->match("xbyczd", $match));
		$this->assertEqual($match, "c");
	}
}


class TestOfLexerStateStack extends UnitTestCase {
	function TestOfLexerStateStack() {
		$this->UnitTestCase();
	}
	function testStartState() {
		$stack = new Doku_LexerStateStack("one");
		$this->assertEqual($stack->getCurrent(), "one");
	}
	function testExhaustion() {
		$stack = new Doku_LexerStateStack("one");
		$this->assertFalse($stack->leave());
	}
	function testStateMoves() {
		$stack = new Doku_LexerStateStack("one");
		$stack->enter("two");
		$this->assertEqual($stack->getCurrent(), "two");
		$stack->enter("three");
		$this->assertEqual($stack->getCurrent(), "three");
		$this->assertTrue($stack->leave());
		$this->assertEqual($stack->getCurrent(), "two");
		$stack->enter("third");
		$this->assertEqual($stack->getCurrent(), "third");
		$this->assertTrue($stack->leave());
		$this->assertTrue($stack->leave());
		$this->assertEqual($stack->getCurrent(), "one");
	}
}

class TestParser {
	function TestParser() {
	}
	function accept() {
	}
	function a() {
	}
	function b() {
	}
}
Mock::generate('TestParser');

class TestOfLexer extends UnitTestCase {
	function TestOfLexer() {
		$this->UnitTestCase();
	}
	function testNoPatterns() {
		$handler = new MockTestParser($this);
		$handler->expectNever("accept");
		$handler->setReturnValue("accept", true);
		$lexer = new Doku_Lexer($handler);
		$this->assertFalse($lexer->parse("abcdef"));
	}
	function testEmptyPage() {
		$handler = new MockTestParser($this);
		$handler->expectNever("accept");
		$handler->setReturnValue("accept", true);
		$handler->expectNever("accept");
		$handler->setReturnValue("accept", true);
		$lexer = new Doku_Lexer($handler);
		$lexer->addPattern("a+");
		$this->assertTrue($lexer->parse(""));
	}
	function testSinglePattern() {
		$handler = new MockTestParser($this);
		$handler->expectArgumentsAt(0, "accept", array("aaa", DOKU_LEXER_MATCHED, 0));
		$handler->expectArgumentsAt(1, "accept", array("x", DOKU_LEXER_UNMATCHED, 3));
		$handler->expectArgumentsAt(2, "accept", array("a", DOKU_LEXER_MATCHED, 4));
		$handler->expectArgumentsAt(3, "accept", array("yyy", DOKU_LEXER_UNMATCHED, 5));
		$handler->expectArgumentsAt(4, "accept", array("a", DOKU_LEXER_MATCHED, 8));
		$handler->expectArgumentsAt(5, "accept", array("x", DOKU_LEXER_UNMATCHED, 9));
		$handler->expectArgumentsAt(6, "accept", array("aaa", DOKU_LEXER_MATCHED, 10));
		$handler->expectArgumentsAt(7, "accept", array("z", DOKU_LEXER_UNMATCHED, 13));
		$handler->expectCallCount("accept", 8);
		$handler->setReturnValue("accept", true);
		$lexer = new Doku_Lexer($handler);
		$lexer->addPattern("a+");
		$this->assertTrue($lexer->parse("aaaxayyyaxaaaz"));
		$handler->tally();
	}
	function testMultiplePattern() {
		$handler = new MockTestParser($this);
		$target = array("a", "b", "a", "bb", "x", "b", "a", "xxxxxx", "a", "x");
		$positions = array(0,1,2,3,5,6,7,8,14,15);
		for ($i = 0; $i < count($target); $i++) {
			$handler->expectArgumentsAt($i, "accept", array($target[$i], '*', $positions[$i]));
		}
		$handler->expectCallCount("accept", count($target));
		$handler->setReturnValue("accept", true);
		$lexer = new Doku_Lexer($handler);
		$lexer->addPattern("a+");
		$lexer->addPattern("b+");
		$this->assertTrue($lexer->parse("ababbxbaxxxxxxax"));
		$handler->tally();
	}
}

class TestOfLexerModes extends UnitTestCase {
	function TestOfLexerModes() {
		$this->UnitTestCase();
	}
	function testIsolatedPattern() {
		$handler = new MockTestParser($this);
		$handler->expectArgumentsAt(0, "a", array("a", DOKU_LEXER_MATCHED,0));
		$handler->expectArgumentsAt(1, "a", array("b", DOKU_LEXER_UNMATCHED,1));
		$handler->expectArgumentsAt(2, "a", array("aa", DOKU_LEXER_MATCHED,2));
		$handler->expectArgumentsAt(3, "a", array("bxb", DOKU_LEXER_UNMATCHED,4));
		$handler->expectArgumentsAt(4, "a", array("aaa", DOKU_LEXER_MATCHED,7));
		$handler->expectArgumentsAt(5, "a", array("x", DOKU_LEXER_UNMATCHED,10));
		$handler->expectArgumentsAt(6, "a", array("aaaa", DOKU_LEXER_MATCHED,11));
		$handler->expectArgumentsAt(7, "a", array("x", DOKU_LEXER_UNMATCHED,15));
		$handler->expectCallCount("a", 8);
		$handler->setReturnValue("a", true);
		$lexer = new Doku_Lexer($handler, "a");
		$lexer->addPattern("a+", "a");
		$lexer->addPattern("b+", "b");
		$this->assertTrue($lexer->parse("abaabxbaaaxaaaax"));
		$handler->tally();
	}
	function testModeChange() {
		$handler = new MockTestParser($this);
		$handler->expectArgumentsAt(0, "a", array("a", DOKU_LEXER_MATCHED,0));
		$handler->expectArgumentsAt(1, "a", array("b", DOKU_LEXER_UNMATCHED,1));
		$handler->expectArgumentsAt(2, "a", array("aa", DOKU_LEXER_MATCHED,2));
		$handler->expectArgumentsAt(3, "a", array("b", DOKU_LEXER_UNMATCHED,4));
		$handler->expectArgumentsAt(4, "a", array("aaa", DOKU_LEXER_MATCHED,5));
		$handler->expectArgumentsAt(0, "b", array(":", DOKU_LEXER_ENTER,8));
		$handler->expectArgumentsAt(1, "b", array("a", DOKU_LEXER_UNMATCHED,9));
		$handler->expectArgumentsAt(2, "b", array("b", DOKU_LEXER_MATCHED, 10));
		$handler->expectArgumentsAt(3, "b", array("a", DOKU_LEXER_UNMATCHED,11));
		$handler->expectArgumentsAt(4, "b", array("bb", DOKU_LEXER_MATCHED,12));
		$handler->expectArgumentsAt(5, "b", array("a", DOKU_LEXER_UNMATCHED,14));
		$handler->expectArgumentsAt(6, "b", array("bbb", DOKU_LEXER_MATCHED,15));
		$handler->expectArgumentsAt(7, "b", array("a", DOKU_LEXER_UNMATCHED,18));
		$handler->expectCallCount("a", 5);
		$handler->expectCallCount("b", 8);
		$handler->setReturnValue("a", true);
		$handler->setReturnValue("b", true);
		$lexer = new Doku_Lexer($handler, "a");
		$lexer->addPattern("a+", "a");
		$lexer->addEntryPattern(":", "a", "b");
		$lexer->addPattern("b+", "b");
		$this->assertTrue($lexer->parse("abaabaaa:ababbabbba"));
		$handler->tally();
	}
	function testNesting() {
		$handler = new MockTestParser($this);
		$handler->setReturnValue("a", true);
		$handler->setReturnValue("b", true);
		$handler->expectArgumentsAt(0, "a", array("aa", DOKU_LEXER_MATCHED,0));
		$handler->expectArgumentsAt(1, "a", array("b", DOKU_LEXER_UNMATCHED,2));
		$handler->expectArgumentsAt(2, "a", array("aa", DOKU_LEXER_MATCHED,3));
		$handler->expectArgumentsAt(3, "a", array("b", DOKU_LEXER_UNMATCHED,5));
		$handler->expectArgumentsAt(0, "b", array("(", DOKU_LEXER_ENTER,6));
		$handler->expectArgumentsAt(1, "b", array("bb", DOKU_LEXER_MATCHED,7));
		$handler->expectArgumentsAt(2, "b", array("a", DOKU_LEXER_UNMATCHED,9));
		$handler->expectArgumentsAt(3, "b", array("bb", DOKU_LEXER_MATCHED,10));
		$handler->expectArgumentsAt(4, "b", array(")", DOKU_LEXER_EXIT,12));
		$handler->expectArgumentsAt(4, "a", array("aa", DOKU_LEXER_MATCHED,13));
		$handler->expectArgumentsAt(5, "a", array("b", DOKU_LEXER_UNMATCHED,15));
		$handler->expectCallCount("a", 6);
		$handler->expectCallCount("b", 5);
		$lexer = new Doku_Lexer($handler, "a");
		$lexer->addPattern("a+", "a");
		$lexer->addEntryPattern("(", "a", "b");
		$lexer->addPattern("b+", "b");
		$lexer->addExitPattern(")", "b");
		$this->assertTrue($lexer->parse("aabaab(bbabb)aab"));
		$handler->tally();
	}
	function testSingular() {
		$handler = new MockTestParser($this);
		$handler->setReturnValue("a", true);
		$handler->setReturnValue("b", true);
		$handler->expectArgumentsAt(0, "a", array("aa", DOKU_LEXER_MATCHED,0));
		$handler->expectArgumentsAt(1, "a", array("aa", DOKU_LEXER_MATCHED,3));
		$handler->expectArgumentsAt(2, "a", array("xx", DOKU_LEXER_UNMATCHED,5));
		$handler->expectArgumentsAt(3, "a", array("xx", DOKU_LEXER_UNMATCHED,10));
		$handler->expectArgumentsAt(0, "b", array("b", DOKU_LEXER_SPECIAL,2));
		$handler->expectArgumentsAt(1, "b", array("bbb", DOKU_LEXER_SPECIAL,7));
		$handler->expectCallCount("a", 4);
		$handler->expectCallCount("b", 2);
		$lexer = new Doku_Lexer($handler, "a");
		$lexer->addPattern("a+", "a");
		$lexer->addSpecialPattern("b+", "a", "b");
		$this->assertTrue($lexer->parse("aabaaxxbbbxx"));
		$handler->tally();
	}
	function testUnwindTooFar() {
		$handler = new MockTestParser($this);
		$handler->setReturnValue("a", true);
		$handler->expectArgumentsAt(0, "a", array("aa", DOKU_LEXER_MATCHED,0));
		$handler->expectArgumentsAt(1, "a", array(")", DOKU_LEXER_EXIT,2));
		$handler->expectCallCount("a", 2);
		$lexer = new Doku_Lexer($handler, "a");
		$lexer->addPattern("a+", "a");
		$lexer->addExitPattern(")", "a");
		$this->assertFalse($lexer->parse("aa)aa"));
		$handler->tally();
	}
}

class TestOfLexerHandlers extends UnitTestCase {
	function TestOfLexerHandlers() {
		$this->UnitTestCase();
	}
	function testModeMapping() {
		$handler = new MockTestParser($this);
		$handler->setReturnValue("a", true);
		$handler->expectArgumentsAt(0, "a", array("aa", DOKU_LEXER_MATCHED,0));
		$handler->expectArgumentsAt(1, "a", array("(", DOKU_LEXER_ENTER,2));
		$handler->expectArgumentsAt(2, "a", array("bb", DOKU_LEXER_MATCHED,3));
		$handler->expectArgumentsAt(3, "a", array("a", DOKU_LEXER_UNMATCHED,5));
		$handler->expectArgumentsAt(4, "a", array("bb", DOKU_LEXER_MATCHED,6));
		$handler->expectArgumentsAt(5, "a", array(")", DOKU_LEXER_EXIT,8));
		$handler->expectArgumentsAt(6, "a", array("b", DOKU_LEXER_UNMATCHED,9));
		$handler->expectCallCount("a", 7);
		$lexer = new Doku_Lexer($handler, "mode_a");
		$lexer->addPattern("a+", "mode_a");
		$lexer->addEntryPattern("(", "mode_a", "mode_b");
		$lexer->addPattern("b+", "mode_b");
		$lexer->addExitPattern(")", "mode_b");
		$lexer->mapHandler("mode_a", "a");
		$lexer->mapHandler("mode_b", "a");
		$this->assertTrue($lexer->parse("aa(bbabb)b"));
		$handler->tally();
	}
}

class TestParserByteIndex {
    
	function TestParserByteIndex() {}
    
	function ignore() {}
    
	function caught() {}
}

Mock::generate('TestParserByteIndex');

class TestOfLexerByteIndices extends UnitTestCase {
    
	function TestOfLexerByteIndices() {
		$this->UnitTestCase();
	}
    
	function testIndex() {
        $doc = "aaa<file>bcd</file>eee";
        
		$handler = new MockTestParserByteIndex($this);
		$handler->setReturnValue("ignore", true);
        $handler->setReturnValue("caught", true);
        
		$handler->expectArgumentsAt(
            0,
            "caught",
            array("<file>", DOKU_LEXER_ENTER, strpos($doc,'<file>'))
            );
		$handler->expectArgumentsAt(
            1,
            "caught",
            array("b", DOKU_LEXER_SPECIAL, strpos($doc,'b'))
            );
		$handler->expectArgumentsAt(
            2,
            "caught",
            array("c", DOKU_LEXER_MATCHED, strpos($doc,'c'))
            );
        $handler->expectArgumentsAt(
            3,
            "caught",
            array("d", DOKU_LEXER_UNMATCHED, strpos($doc,'d'))
            );
		$handler->expectArgumentsAt(
            4,
            "caught",
            array("</file>", DOKU_LEXER_EXIT, strpos($doc,'</file>'))
            );
		$handler->expectCallCount("caught", 5);
        
		$lexer = new Doku_Lexer($handler, "ignore");
		$lexer->addEntryPattern("<file>", "ignore", "caught");
		$lexer->addExitPattern("</file>", "caught");
        $lexer->addSpecialPattern('b','caught','special');
        $lexer->mapHandler('special','caught');
        $lexer->addPattern('c','caught');
        
		$this->assertTrue($lexer->parse($doc));
		$handler->tally();
	}
    
	function testIndexLookaheadEqual() {
        $doc = "aaa<file>bcd</file>eee";
        
		$handler = new MockTestParserByteIndex($this);
		$handler->setReturnValue("ignore", true);
        $handler->setReturnValue("caught", true);
        
		$handler->expectArgumentsAt(
            0,
            "caught",
            array("<file>", DOKU_LEXER_ENTER, strpos($doc,'<file>'))
            );
		$handler->expectArgumentsAt(
            1,
            "caught",
            array("b", DOKU_LEXER_SPECIAL, strpos($doc,'b'))
            );
		$handler->expectArgumentsAt(
            2,
            "caught",
            array("c", DOKU_LEXER_MATCHED, strpos($doc,'c'))
            );
        $handler->expectArgumentsAt(
            3,
            "caught",
            array("d", DOKU_LEXER_UNMATCHED, strpos($doc,'d'))
            );
		$handler->expectArgumentsAt(
            4,
            "caught",
            array("</file>", DOKU_LEXER_EXIT, strpos($doc,'</file>'))
            );
		$handler->expectCallCount("caught", 5);
        
		$lexer = new Doku_Lexer($handler, "ignore");
		$lexer->addEntryPattern('<file>(?=.*</file>)', "ignore", "caught");
		$lexer->addExitPattern("</file>", "caught");
        $lexer->addSpecialPattern('b','caught','special');
        $lexer->mapHandler('special','caught');
        $lexer->addPattern('c','caught');
        
		$this->assertTrue($lexer->parse($doc));
		$handler->tally();
	}
    
	function testIndexLookaheadNotEqual() {
        $doc = "aaa<file>bcd</file>eee";
        
		$handler = new MockTestParserByteIndex($this);
		$handler->setReturnValue("ignore", true);
        $handler->setReturnValue("caught", true);
        
		$handler->expectArgumentsAt(
            0,
            "caught",
            array("<file>", DOKU_LEXER_ENTER, strpos($doc,'<file>'))
            );
		$handler->expectArgumentsAt(
            1,
            "caught",
            array("b", DOKU_LEXER_SPECIAL, strpos($doc,'b'))
            );
		$handler->expectArgumentsAt(
            2,
            "caught",
            array("c", DOKU_LEXER_MATCHED, strpos($doc,'c'))
            );
        $handler->expectArgumentsAt(
            3,
            "caught",
            array("d", DOKU_LEXER_UNMATCHED, strpos($doc,'d'))
            );
		$handler->expectArgumentsAt(
            4,
            "caught",
            array("</file>", DOKU_LEXER_EXIT, strpos($doc,'</file>'))
            );
		$handler->expectCallCount("caught", 5);
        
		$lexer = new Doku_Lexer($handler, "ignore");
		$lexer->addEntryPattern('<file>(?!foo)', "ignore", "caught");
		$lexer->addExitPattern("</file>", "caught");
        $lexer->addSpecialPattern('b','caught','special');
        $lexer->mapHandler('special','caught');
        $lexer->addPattern('c','caught');
        
		$this->assertTrue($lexer->parse($doc));
		$handler->tally();
	}
    
	function testIndexLookbehindEqual() {
        $doc = "aaa<file>bcd</file>eee";
        
		$handler = new MockTestParserByteIndex($this);
		$handler->setReturnValue("ignore", true);
        $handler->setReturnValue("caught", true);
        
		$handler->expectArgumentsAt(
            0,
            "caught",
            array("<file>", DOKU_LEXER_ENTER, strpos($doc,'<file>'))
            );
		$handler->expectArgumentsAt(
            1,
            "caught",
            array("b", DOKU_LEXER_SPECIAL, strpos($doc,'b'))
            );
		$handler->expectArgumentsAt(
            2,
            "caught",
            array("c", DOKU_LEXER_MATCHED, strpos($doc,'c'))
            );
        $handler->expectArgumentsAt(
            3,
            "caught",
            array("d", DOKU_LEXER_UNMATCHED, strpos($doc,'d'))
            );
		$handler->expectArgumentsAt(
            4,
            "caught",
            array("</file>", DOKU_LEXER_EXIT, strpos($doc,'</file>'))
            );
		$handler->expectCallCount("caught", 5);
        
		$lexer = new Doku_Lexer($handler, "ignore");
		$lexer->addEntryPattern('<file>', "ignore", "caught");
		$lexer->addExitPattern("(?<=d)</file>", "caught");
        $lexer->addSpecialPattern('b','caught','special');
        $lexer->mapHandler('special','caught');
        $lexer->addPattern('c','caught');
        
		$this->assertTrue($lexer->parse($doc));
		$handler->tally();
	}
    
	function testIndexLookbehindNotEqual() {
        $doc = "aaa<file>bcd</file>eee";
        
		$handler = new MockTestParserByteIndex($this);
		$handler->setReturnValue("ignore", true);
        $handler->setReturnValue("caught", true);
        
		$handler->expectArgumentsAt(
            0,
            "caught",
            array("<file>", DOKU_LEXER_ENTER, strpos($doc,'<file>'))
            );
		$handler->expectArgumentsAt(
            1,
            "caught",
            array("b", DOKU_LEXER_SPECIAL, strpos($doc,'b'))
            );
		$handler->expectArgumentsAt(
            2,
            "caught",
            array("c", DOKU_LEXER_MATCHED, strpos($doc,'c'))
            );
        $handler->expectArgumentsAt(
            3,
            "caught",
            array("d", DOKU_LEXER_UNMATCHED, strpos($doc,'d'))
            );
		$handler->expectArgumentsAt(
            4,
            "caught",
            array("</file>", DOKU_LEXER_EXIT, strpos($doc,'</file>'))
            );
		$handler->expectCallCount("caught", 5);
        
		$lexer = new Doku_Lexer($handler, "ignore");
		$lexer->addEntryPattern('<file>', "ignore", "caught");
		$lexer->addExitPattern("(?<!c)</file>", "caught");
        $lexer->addSpecialPattern('b','caught','special');
        $lexer->mapHandler('special','caught');
        $lexer->addPattern('c','caught');
        
		$this->assertTrue($lexer->parse($doc));
		$handler->tally();
	}

    /**
     * This test is primarily to ensure the correct match is chosen
     * when there are non-captured elements in the pattern.
     */
    function testIndexSelectCorrectMatch() {
        $doc = "ALL FOOLS ARE FOO";
        $pattern = '\bFOO\b';

        $handler = new MockTestParserByteIndex($this);
        $handler->setReturnValue("ignore", true);
        $handler->setReturnValue("caught", true);

        $matches = array();
        preg_match('/'.$pattern.'/',$doc,$matches,PREG_OFFSET_CAPTURE);

        $handler->expectArgumentsAt(
            0,
            "caught",
            array("FOO", DOKU_LEXER_SPECIAL, $matches[0][1])
            );
        $handler->expectCallCount("caught", 1);

        $lexer = new Doku_Lexer($handler, "ignore");
        $lexer->addSpecialPattern($pattern,'ignore','caught');

        $this->assertTrue($lexer->parse($doc));
        $handler->tally();
    }

}

?>
