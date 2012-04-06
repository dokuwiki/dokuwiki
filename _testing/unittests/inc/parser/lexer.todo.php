<?php
/**
* @version $Id: lexer.todo.php,v 1.2 2005/03/25 21:00:22 harryf Exp $
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
class TestOfLexerParallelRegex extends PHPUnit_Framework_TestCase {

	function testNoPatterns() {
		$regex = new Doku_LexerParallelRegex(false);
		$this->assertFalse($regex->match("Hello", $match));
		$this->assertEquals($match, "");
	}
	function testNoSubject() {
		$regex = new Doku_LexerParallelRegex(false);
		$regex->addPattern(".*");
		$this->assertTrue($regex->match("", $match));
		$this->assertEquals($match, "");
	}
	function testMatchAll() {
		$regex = new Doku_LexerParallelRegex(false);
		$regex->addPattern(".*");
		$this->assertTrue($regex->match("Hello", $match));
		$this->assertEquals($match, "Hello");
	}
	function testCaseSensitive() {
		$regex = new Doku_LexerParallelRegex(true);
		$regex->addPattern("abc");
		$this->assertTrue($regex->match("abcdef", $match));
		$this->assertEquals($match, "abc");
		$this->assertTrue($regex->match("AAABCabcdef", $match));
		$this->assertEquals($match, "abc");
	}
	function testCaseInsensitive() {
		$regex = new Doku_LexerParallelRegex(false);
		$regex->addPattern("abc");
		$this->assertTrue($regex->match("abcdef", $match));
		$this->assertEquals($match, "abc");
		$this->assertTrue($regex->match("AAABCabcdef", $match));
		$this->assertEquals($match, "ABC");
	}
	function testMatchMultiple() {
		$regex = new Doku_LexerParallelRegex(true);
		$regex->addPattern("abc");
		$regex->addPattern("ABC");
		$this->assertTrue($regex->match("abcdef", $match));
		$this->assertEquals($match, "abc");
		$this->assertTrue($regex->match("AAABCabcdef", $match));
		$this->assertEquals($match, "ABC");
		$this->assertFalse($regex->match("Hello", $match));
	}
	function testPatternLabels() {
		$regex = new Doku_LexerParallelRegex(false);
		$regex->addPattern("abc", "letter");
		$regex->addPattern("123", "number");
		$this->assertIdentical($regex->match("abcdef", $match), "letter");
		$this->assertEquals($match, "abc");
		$this->assertIdentical($regex->match("0123456789", $match), "number");
		$this->assertEquals($match, "123");
	}
	function testMatchMultipleWithLookaheadNot() {
		$regex = new Doku_LexerParallelRegex(true);
		$regex->addPattern("abc");
		$regex->addPattern("ABC");
		$regex->addPattern("a(?!\n).{1}");
		$this->assertTrue($regex->match("abcdef", $match));
		$this->assertEquals($match, "abc");
		$this->assertTrue($regex->match("AAABCabcdef", $match));
		$this->assertEquals($match, "ABC");
		$this->assertTrue($regex->match("a\nab", $match));
		$this->assertEquals($match, "ab");
		$this->assertFalse($regex->match("Hello", $match));
	}
	function testMatchSetOptionCaseless() {
		$regex = new Doku_LexerParallelRegex(true);
		$regex->addPattern("a(?i)b(?i)c");
		$this->assertTrue($regex->match("aBc", $match));
		$this->assertEquals($match, "aBc");
	}
	function testMatchSetOptionUngreedy() {
		$regex = new Doku_LexerParallelRegex(true);
		$regex->addPattern("(?U)\w+");
		$this->assertTrue($regex->match("aaaaaa", $match));
		$this->assertEquals($match, "a");
	}
	function testMatchLookaheadEqual() {
		$regex = new Doku_LexerParallelRegex(true);
		$regex->addPattern("\w(?=c)");
		$this->assertTrue($regex->match("xbyczd", $match));
		$this->assertEquals($match, "y");
	}
	function testMatchLookaheadNot() {
		$regex = new Doku_LexerParallelRegex(true);
		$regex->addPattern("\w(?!b|c)");
		$this->assertTrue($regex->match("xbyczd", $match));
		$this->assertEquals($match, "b");
	}
	function testMatchLookbehindEqual() {
		$regex = new Doku_LexerParallelRegex(true);
		$regex->addPattern("(?<=c)\w");
		$this->assertTrue($regex->match("xbyczd", $match));
		$this->assertEquals($match, "z");
	}
	function testMatchLookbehindNot() {
		$regex = new Doku_LexerParallelRegex(true);
		$regex->addPattern("(?<!\A|x|b)\w");
		$this->assertTrue($regex->match("xbyczd", $match));
		$this->assertEquals($match, "c");
	}
}


class TestOfLexerStateStack extends PHPUnit_Framework_TestCase {
	function testStartState() {
		$stack = new Doku_LexerStateStack("one");
		$this->assertEquals($stack->getCurrent(), "one");
	}
	function testExhaustion() {
		$stack = new Doku_LexerStateStack("one");
		$this->assertFalse($stack->leave());
	}
	function testStateMoves() {
		$stack = new Doku_LexerStateStack("one");
		$stack->enter("two");
		$this->assertEquals($stack->getCurrent(), "two");
		$stack->enter("three");
		$this->assertEquals($stack->getCurrent(), "three");
		$this->assertTrue($stack->leave());
		$this->assertEquals($stack->getCurrent(), "two");
		$stack->enter("third");
		$this->assertEquals($stack->getCurrent(), "third");
		$this->assertTrue($stack->leave());
		$this->assertTrue($stack->leave());
		$this->assertEquals($stack->getCurrent(), "one");
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

class TestOfLexer extends PHPUnit_Framework_TestCase {
	function testNoPatterns() {
        $handler = $this->getMock('TestParser');
        $handler->expects($this->never())->method('accept');
		$lexer = new Doku_Lexer($handler);
		$this->assertFalse($lexer->parse("abcdef"));
	}
	function testEmptyPage() {
        $handler = $this->getMock('TestParser');
        $handler->expects($this->never())->method('accept');
		$lexer = new Doku_Lexer($handler);
		$lexer->addPattern("a+");
		$this->assertTrue($lexer->parse(""));
	}
	function testSinglePattern() {
        $handler = $this->getMock('TestParser');
        $handler->expects($this->at(0))->method('accept')
            ->with("aaa", DOKU_LEXER_MATCHED, 0)->will($this->returnValue(true));
        $handler->expects($this->at(1))->method('accept')
            ->with("x", DOKU_LEXER_UNMATCHED, 3)->will($this->returnValue(true));
        $handler->expects($this->at(2))->method('accept')
            ->with("a", DOKU_LEXER_MATCHED, 4)->will($this->returnValue(true));
        $handler->expects($this->at(3))->method('accept')
            ->with("yyy", DOKU_LEXER_UNMATCHED, 5)->will($this->returnValue(true));
        $handler->expects($this->at(4))->method('accept')
            ->with("a", DOKU_LEXER_MATCHED, 8)->will($this->returnValue(true));
        $handler->expects($this->at(5))->method('accept')
            ->with("x", DOKU_LEXER_UNMATCHED, 9)->will($this->returnValue(true));
        $handler->expects($this->at(6))->method('accept')
            ->with("aaa", DOKU_LEXER_MATCHED, 10)->will($this->returnValue(true));
        $handler->expects($this->at(7))->method('accept')
            ->with("z", DOKU_LEXER_UNMATCHED, 13)->will($this->returnValue(true));

		$lexer = new Doku_Lexer($handler);
		$lexer->addPattern("a+");
		$this->assertTrue($lexer->parse("aaaxayyyaxaaaz"));
	}
	function testMultiplePattern() {
		$handler = $this->getMock('TestParser', array('accept'));
		$target = array("a", "b", "a", "bb", "x", "b", "a", "xxxxxx", "a", "x");
		$positions = array(0, 1, 2, 3, 5, 6, 7, 8, 14, 15);
		for ($i = 0; $i < count($target); $i++) {
            $handler->expects($this->at($i))->method('accept')
                ->with($target[$i], $this->anything(), $positions[$i])->will($this->returnValue(true));
		}
		$lexer = new Doku_Lexer($handler);
		$lexer->addPattern("a+");
		$lexer->addPattern("b+");
		$this->assertTrue($lexer->parse("ababbxbaxxxxxxax"));
	}
}

class TestOfLexerModes extends PHPUnit_Framework_TestCase {
	function testIsolatedPattern() {
        $handler = $this->getMock('TestParser');
        $handler->expects($this->at(0))->method('a')
            ->with("a", DOKU_LEXER_MATCHED,0)->will($this->returnValue(true));
        $handler->expects($this->at(1))->method('a')
            ->with("b", DOKU_LEXER_UNMATCHED,1)->will($this->returnValue(true));
        $handler->expects($this->at(2))->method('a')
            ->with("aa", DOKU_LEXER_MATCHED,2)->will($this->returnValue(true));
        $handler->expects($this->at(3))->method('a')
            ->with("bxb", DOKU_LEXER_UNMATCHED,4)->will($this->returnValue(true));
        $handler->expects($this->at(4))->method('a')
            ->with("aaa", DOKU_LEXER_MATCHED,7)->will($this->returnValue(true));
        $handler->expects($this->at(5))->method('a')
            ->with("x", DOKU_LEXER_UNMATCHED,10)->will($this->returnValue(true));
        $handler->expects($this->at(6))->method('a')
            ->with("aaaa", DOKU_LEXER_MATCHED,11)->will($this->returnValue(true));
        $handler->expects($this->at(7))->method('a')
            ->with("x", DOKU_LEXER_UNMATCHED,15)->will($this->returnValue(true));
		$lexer = new Doku_Lexer($handler, "a");
		$lexer->addPattern("a+", "a");
		$lexer->addPattern("b+", "b");
		$this->assertTrue($lexer->parse("abaabxbaaaxaaaax"));
	}
	function testModeChange() {
        $handler = $this->getMock('TestParser');
        $handler->expects($this->at(0))->method('a')
            ->with("a", DOKU_LEXER_MATCHED,0)->will($this->returnValue(true));
        $handler->expects($this->at(1))->method('a')
            ->with("b", DOKU_LEXER_UNMATCHED,1)->will($this->returnValue(true));
        $handler->expects($this->at(2))->method('a')
            ->with("aa", DOKU_LEXER_MATCHED,2)->will($this->returnValue(true));
        $handler->expects($this->at(3))->method('a')
            ->with("b", DOKU_LEXER_UNMATCHED,4)->will($this->returnValue(true));
        $handler->expects($this->at(4))->method('a')
            ->with("aaa", DOKU_LEXER_MATCHED,5)->will($this->returnValue(true));

        $handler->expects($this->at(0))->method('b')
            ->with(":", DOKU_LEXER_ENTER,8)->will($this->returnValue(true));
        $handler->expects($this->at(1))->method('b')
            ->with("a", DOKU_LEXER_UNMATCHED,9)->will($this->returnValue(true));
        $handler->expects($this->at(2))->method('b')
            ->with("b", DOKU_LEXER_MATCHED, 10)->will($this->returnValue(true));
        $handler->expects($this->at(3))->method('b')
            ->with("a", DOKU_LEXER_UNMATCHED,11)->will($this->returnValue(true));
        $handler->expects($this->at(4))->method('b')
            ->with("bb", DOKU_LEXER_MATCHED,12)->will($this->returnValue(true));
        $handler->expects($this->at(5))->method('b')
            ->with("a", DOKU_LEXER_UNMATCHED,14)->will($this->returnValue(true));
        $handler->expects($this->at(6))->method('b')
            ->with("bbb", DOKU_LEXER_MATCHED,15)->will($this->returnValue(true));
        $handler->expects($this->at(7))->method('b')
            ->with("a", DOKU_LEXER_UNMATCHED,18)->will($this->returnValue(true));

		$lexer = new Doku_Lexer($handler, "a");
		$lexer->addPattern("a+", "a");
		$lexer->addEntryPattern(":", "a", "b");
		$lexer->addPattern("b+", "b");
		$this->assertTrue($lexer->parse("abaabaaa:ababbabbba"));
	}
	function testNesting() {
        $handler = $this->getMock('TestParser');
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
		$lexer = new Doku_Lexer($handler, "a");
		$lexer->addPattern("a+", "a");
		$lexer->addEntryPattern("(", "a", "b");
		$lexer->addPattern("b+", "b");
		$lexer->addExitPattern(")", "b");
		$this->assertTrue($lexer->parse("aabaab(bbabb)aab"));
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

class TestOfLexerHandlers extends PHPUnit_Framework_TestCase {
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

class TestOfLexerByteIndices extends PHPUnit_Framework_TestCase {

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
