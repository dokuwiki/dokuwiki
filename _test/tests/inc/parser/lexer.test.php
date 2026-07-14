<?php
/**
* @version $Id: lexer.todo.php,v 1.2 2005/03/25 21:00:22 harryf Exp $
* @package Doku
* @subpackage Tests
*/

use dokuwiki\Parsing\Lexer\Lexer;
use dokuwiki\Parsing\Lexer\ParallelRegex;
use dokuwiki\Parsing\Lexer\StateStack;

/**
* @package Doku
* @subpackage Tests
*/
class TestOfLexerParallelRegex extends DokuWikiTest {

    function testNoPatterns() {
        $regex = new ParallelRegex(false);
        $this->assertFalse($regex->apply("Hello", $match));
        $this->assertEquals($match, "");
    }
    function testNoSubject() {
        $regex = new ParallelRegex(false);
        $regex->addPattern(".*");
        $this->assertTrue($regex->apply("", $match));
        $this->assertEquals($match, "");
    }
    function testMatchAll() {
        $regex = new ParallelRegex(false);
        $regex->addPattern(".*");
        $this->assertTrue($regex->apply("Hello", $match));
        $this->assertEquals($match, "Hello");
    }
    function testCaseSensitive() {
        $regex = new ParallelRegex(true);
        $regex->addPattern("abc");
        $this->assertTrue($regex->apply("abcdef", $match));
        $this->assertEquals($match, "abc");
        $this->assertTrue($regex->apply("AAABCabcdef", $match));
        $this->assertEquals($match, "abc");
    }
    function testCaseInsensitive() {
        $regex = new ParallelRegex(false);
        $regex->addPattern("abc");
        $this->assertTrue($regex->apply("abcdef", $match));
        $this->assertEquals($match, "abc");
        $this->assertTrue($regex->apply("AAABCabcdef", $match));
        $this->assertEquals($match, "ABC");
    }
    function testMatchMultiple() {
        $regex = new ParallelRegex(true);
        $regex->addPattern("abc");
        $regex->addPattern("ABC");
        $this->assertTrue($regex->apply("abcdef", $match));
        $this->assertEquals($match, "abc");
        $this->assertTrue($regex->apply("AAABCabcdef", $match));
        $this->assertEquals($match, "ABC");
        $this->assertFalse($regex->apply("Hello", $match));
    }
    function testPatternLabels() {
        $regex = new ParallelRegex(false);
        $regex->addPattern("abc", "letter");
        $regex->addPattern("123", "number");
        $this->assertEquals($regex->apply("abcdef", $match), "letter");
        $this->assertEquals($match, "abc");
        $this->assertEquals($regex->apply("0123456789", $match), "number");
        $this->assertEquals($match, "123");
    }
    function testMatchMultipleWithLookaheadNot() {
        $regex = new ParallelRegex(true);
        $regex->addPattern("abc");
        $regex->addPattern("ABC");
        $regex->addPattern("a(?!\n).{1}");
        $this->assertTrue($regex->apply("abcdef", $match));
        $this->assertEquals($match, "abc");
        $this->assertTrue($regex->apply("AAABCabcdef", $match));
        $this->assertEquals($match, "ABC");
        $this->assertTrue($regex->apply("a\nab", $match));
        $this->assertEquals($match, "ab");
        $this->assertFalse($regex->apply("Hello", $match));
    }
    function testMatchSetOptionCaseless() {
        $regex = new ParallelRegex(true);
        $regex->addPattern("a(?i)b(?i)c");
        $this->assertTrue($regex->apply("aBc", $match));
        $this->assertEquals($match, "aBc");
    }
    function testMatchSetOptionUngreedy() {
        $regex = new ParallelRegex(true);
        $regex->addPattern("(?U)\w+");
        $this->assertTrue($regex->apply("aaaaaa", $match));
        $this->assertEquals($match, "a");
    }
    function testMatchLookaheadEqual() {
        $regex = new ParallelRegex(true);
        $regex->addPattern("\w(?=c)");
        $this->assertTrue($regex->apply("xbyczd", $match));
        $this->assertEquals($match, "y");
    }
    function testMatchLookaheadNot() {
        $regex = new ParallelRegex(true);
        $regex->addPattern("\w(?!b|c)");
        $this->assertTrue($regex->apply("xbyczd", $match));
        $this->assertEquals($match, "b");
    }
    function testMatchLookbehindEqual() {
        $regex = new ParallelRegex(true);
        $regex->addPattern("(?<=c)\w");
        $this->assertTrue($regex->apply("xbyczd", $match));
        $this->assertEquals($match, "z");
    }
    function testMatchLookbehindNot() {
        $regex = new ParallelRegex(true);
        $regex->addPattern("(?<!\A|x|b)\w");
        $this->assertTrue($regex->apply("xbyczd", $match));
        $this->assertEquals($match, "c");
    }
}


class TestOfLexerStateStack extends DokuWikiTest {
    function testStartState() {
        $stack = new StateStack("one");
        $this->assertEquals($stack->getCurrent(), "one");
    }
    function testExhaustion() {
        $stack = new StateStack("one");
        $this->assertFalse($stack->leave());
    }
    function testStateMoves() {
        $stack = new StateStack("one");
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
    function __construct() {
    }
    function accept() {
    }
    function a() {
    }
    function b() {
    }
}

class TestOfLexer extends DokuWikiTest {
    function testNoPatterns() {
        $handler = $this->createMock('TestParser');
        $handler->expects($this->never())->method('accept');
        $lexer = new Lexer($handler);
        $this->assertFalse($lexer->parse("abcdef"));
    }
    function testEmptyPage() {
        $handler = $this->createMock('TestParser');
        $handler->expects($this->never())->method('accept');
        $lexer = new Lexer($handler);
        $lexer->addPattern("a+");
        $this->assertTrue($lexer->parse(""));
    }
    function testSinglePattern() {
        $acceptArguments = [
            ["aaa", DOKU_LEXER_MATCHED, 0],
            ["x", DOKU_LEXER_UNMATCHED, 3],
            ["a", DOKU_LEXER_MATCHED, 4],
            ["yyy", DOKU_LEXER_UNMATCHED, 5],
            ["a", DOKU_LEXER_MATCHED, 8],
            ["x", DOKU_LEXER_UNMATCHED, 9],
            ["aaa", DOKU_LEXER_MATCHED, 10],
            ["z", DOKU_LEXER_UNMATCHED, 13],
        ];
        $acceptArgumentCount = count($acceptArguments);

        $handler = $this->createMock('TestParser');
        $handler
            ->expects($this->exactly($acceptArgumentCount))
            ->method('accept')
            ->withConsecutive(...$acceptArguments)
            ->willReturnOnConsecutiveCalls(...array_fill(0, $acceptArgumentCount, true));

        $lexer = new Lexer($handler);
        $lexer->addPattern("a+");
        $this->assertTrue($lexer->parse("aaaxayyyaxaaaz"));
    }
    function testMultiplePattern() {
        $acceptArguments = [
            ["a", $this->anything(), 0],
            ["b", $this->anything(), 1],
            ["a", $this->anything(), 2],
            ["bb", $this->anything(), 3],
            ["x", $this->anything(), 5],
            ["b", $this->anything(), 6],
            ["a", $this->anything(), 7],
            ["xxxxxx", $this->anything(), 8],
            ["a", $this->anything(), 14],
            ["x", $this->anything(), 15],
        ];
        $acceptArgumentCount = count($acceptArguments);

        $handler = $this->createPartialMock('TestParser', ['accept']);
        $handler
            ->expects($this->exactly($acceptArgumentCount))
            ->method('accept')
            ->withConsecutive(...$acceptArguments)
            ->willReturnOnConsecutiveCalls(...array_fill(0, $acceptArgumentCount, true));

        $lexer = new Lexer($handler);
        $lexer->addPattern("a+");
        $lexer->addPattern("b+");
        $this->assertTrue($lexer->parse("ababbxbaxxxxxxax"));
    }
}

class TestOfLexerModes extends DokuWikiTest {
    function testIsolatedPattern() {
        $aArguments = [
            ["a", DOKU_LEXER_MATCHED, 0],
            ["b", DOKU_LEXER_UNMATCHED, 1],
            ["aa", DOKU_LEXER_MATCHED, 2],
            ["bxb", DOKU_LEXER_UNMATCHED, 4],
            ["aaa", DOKU_LEXER_MATCHED, 7],
            ["x", DOKU_LEXER_UNMATCHED, 10],
            ["aaaa", DOKU_LEXER_MATCHED, 11],
            ["x", DOKU_LEXER_UNMATCHED, 15],
        ];
        $aArgumentCount = count($aArguments);

        $handler = $this->createMock('TestParser');
        $handler
            ->expects($this->exactly($aArgumentCount))
            ->method('a')
            ->withConsecutive(...$aArguments)
            ->willReturnOnConsecutiveCalls(...array_fill(0, $aArgumentCount, true));

        $lexer = new Lexer($handler, "a");
        $lexer->addPattern("a+", "a");
        $lexer->addPattern("b+", "b");
        $this->assertTrue($lexer->parse("abaabxbaaaxaaaax"));
    }
    function testModeChange() {
        $methodArguments = [
            'a' => [
                ["a", DOKU_LEXER_MATCHED, 0],
                ["b", DOKU_LEXER_UNMATCHED, 1],
                ["aa", DOKU_LEXER_MATCHED, 2],
                ["b", DOKU_LEXER_UNMATCHED, 4],
                ["aaa", DOKU_LEXER_MATCHED, 5],
            ],
            'b' => [
                [":", DOKU_LEXER_ENTER, 8],
                ["a", DOKU_LEXER_UNMATCHED, 9],
                ["b", DOKU_LEXER_MATCHED, 10],
                ["a", DOKU_LEXER_UNMATCHED, 11],
                ["bb", DOKU_LEXER_MATCHED, 12],
                ["a", DOKU_LEXER_UNMATCHED, 14],
                ["bbb", DOKU_LEXER_MATCHED, 15],
                ["a", DOKU_LEXER_UNMATCHED, 18],
            ],
        ];

        $handler = $this->createMock('TestParser');
        foreach ($methodArguments as $method => $arguments) {
            $count = count($arguments);
            $handler
                ->expects($this->exactly($count))
                ->method($method)
                ->withConsecutive(...$arguments)
                ->willReturnOnConsecutiveCalls(...array_fill(0, $count, true));
        }

        $lexer = new Lexer($handler, "a");
        $lexer->addPattern("a+", "a");
        $lexer->addEntryPattern(":", "a", "b");
        $lexer->addPattern("b+", "b");
        $this->assertTrue($lexer->parse("abaabaaa:ababbabbba"));
    }
    function testNesting() {
        $methodArguments = [
            'a' => [
                ["aa", DOKU_LEXER_MATCHED, 0],
                ["b", DOKU_LEXER_UNMATCHED, 2],
                ["aa", DOKU_LEXER_MATCHED, 3],
                ["b", DOKU_LEXER_UNMATCHED, 5],
                // some b calls in between here
                ["aa", DOKU_LEXER_MATCHED, 13],
                ["b", DOKU_LEXER_UNMATCHED, 15],
            ],
            'b' => [
                ["(", DOKU_LEXER_ENTER, 6],
                ["bb", DOKU_LEXER_MATCHED, 7],
                ["a", DOKU_LEXER_UNMATCHED, 9],
                ["bb", DOKU_LEXER_MATCHED, 10],
                [")", DOKU_LEXER_EXIT, 12],
            ],
        ];

        $handler = $this->createMock('TestParser');
        foreach ($methodArguments as $method => $arguments) {
            $count = count($arguments);
            $handler
                ->expects($this->exactly($count))
                ->method($method)
                ->withConsecutive(...$arguments)
                ->willReturnOnConsecutiveCalls(...array_fill(0, $count, true));
        }

        $lexer = new Lexer($handler, "a");
        $lexer->addPattern("a+", "a");
        $lexer->addEntryPattern("(", "a", "b");
        $lexer->addPattern("b+", "b");
        $lexer->addExitPattern(")", "b");
        $this->assertTrue($lexer->parse("aabaab(bbabb)aab"));
    }
    function testSingular() {
        $methodArguments = [
            'a' => [
                ["aa", DOKU_LEXER_MATCHED, 0],
                ["aa", DOKU_LEXER_MATCHED, 3],
                ["xx", DOKU_LEXER_UNMATCHED, 5],
                ["xx", DOKU_LEXER_UNMATCHED, 10],
            ],
            'b' => [
                ["b", DOKU_LEXER_SPECIAL, 2],
                ["bbb", DOKU_LEXER_SPECIAL, 7],
            ],
        ];

        $handler = $this->createMock('TestParser');
        foreach ($methodArguments as $method => $arguments) {
            $count = count($arguments);
            $handler
                ->expects($this->exactly($count))
                ->method($method)
                ->withConsecutive(...$arguments)
                ->willReturnOnConsecutiveCalls(...array_fill(0, $count, true));
        }

        $lexer = new Lexer($handler, "a");
        $lexer->addPattern("a+", "a");
        $lexer->addSpecialPattern("b+", "a", "b");
        $this->assertTrue($lexer->parse("aabaaxxbbbxx"));
    }
    function testUnwindTooFar() {
        $aArguments = [
            ["aa", DOKU_LEXER_MATCHED,0],
            [")", DOKU_LEXER_EXIT,2],
        ];
        $aArgumentCount = count($aArguments);

        $handler = $this->createMock('TestParser');
        $handler
            ->expects($this->exactly($aArgumentCount))
            ->method('a')
            ->withConsecutive(...$aArguments)
            ->willReturnOnConsecutiveCalls(...array_fill(0, $aArgumentCount, true));

        $lexer = new Lexer($handler, "a");
        $lexer->addPattern("a+", "a");
        $lexer->addExitPattern(")", "a");
        $this->assertFalse($lexer->parse("aa)aa"));
    }
}

class TestOfLexerHandlers extends DokuWikiTest {
    function testModeMapping() {
        $aArguments = [
            ["aa", DOKU_LEXER_MATCHED, 0],
            ["(", DOKU_LEXER_ENTER, 2],
            ["bb", DOKU_LEXER_MATCHED, 3],
            ["a", DOKU_LEXER_UNMATCHED, 5],
            ["bb", DOKU_LEXER_MATCHED, 6],
            [")", DOKU_LEXER_EXIT, 8],
            ["b", DOKU_LEXER_UNMATCHED, 9],
        ];
        $aArgumentCount = count($aArguments);

        $handler = $this->createMock('TestParser');
        $handler
            ->expects($this->exactly($aArgumentCount))
            ->method('a')
            ->withConsecutive(...$aArguments)
            ->willReturnOnConsecutiveCalls(...array_fill(0, $aArgumentCount, true));

        $lexer = new Lexer($handler, "mode_a");
        $lexer->addPattern("a+", "mode_a");
        $lexer->addEntryPattern("(", "mode_a", "mode_b");
        $lexer->addPattern("b+", "mode_b");
        $lexer->addExitPattern(")", "mode_b");
        $lexer->mapHandler("mode_a", "a");
        $lexer->mapHandler("mode_b", "a");
        $this->assertTrue($lexer->parse("aa(bbabb)b"));
    }
}

class TestParserByteIndex {

    function __construct() {}

    function ignore() {}

    function caught() {}
}

class TestOfLexerByteIndices extends DokuWikiTest {

    function testIndex() {
        $doc = "aaa<file>bcd</file>eee";

        $caughtArguments = [
            ["<file>", DOKU_LEXER_ENTER, strpos($doc, '<file>')],
            ["b", DOKU_LEXER_SPECIAL, strpos($doc, 'b')],
            ["c", DOKU_LEXER_MATCHED, strpos($doc, 'c')],
            ["d", DOKU_LEXER_UNMATCHED, strpos($doc, 'd')],
            ["</file>", DOKU_LEXER_EXIT, strpos($doc, '</file>')],
        ];
        $caughtArgumentCount = count($caughtArguments);

        $handler = $this->createMock('TestParserByteIndex');
        $handler->expects($this->any())->method('ignore')->will($this->returnValue(true));
        $handler
            ->expects($this->exactly($caughtArgumentCount))
            ->method('caught')
            ->withConsecutive(...$caughtArguments)
            ->willReturnOnConsecutiveCalls(...array_fill(0, $caughtArgumentCount, true));

        $lexer = new Lexer($handler, "ignore");
        $lexer->addEntryPattern("<file>", "ignore", "caught");
        $lexer->addExitPattern("</file>", "caught");
        $lexer->addSpecialPattern('b','caught','special');
        $lexer->mapHandler('special','caught');
        $lexer->addPattern('c','caught');

        $this->assertTrue($lexer->parse($doc));
    }

    function testIndexLookaheadEqual() {
        $doc = "aaa<file>bcd</file>eee";

        $caughtArguments = [
            ["<file>", DOKU_LEXER_ENTER, strpos($doc, '<file>')],
            ["b", DOKU_LEXER_SPECIAL, strpos($doc, 'b')],
            ["c", DOKU_LEXER_MATCHED, strpos($doc, 'c')],
            ["d", DOKU_LEXER_UNMATCHED, strpos($doc, 'd')],
            ["</file>", DOKU_LEXER_EXIT, strpos($doc, '</file>')],
        ];
        $caughtArgumentCount = count($caughtArguments);

        $handler = $this->createMock('TestParserByteIndex');
        $handler->expects($this->any())->method('ignore')->will($this->returnValue(true));
        $handler
            ->expects($this->exactly($caughtArgumentCount))
            ->method('caught')
            ->withConsecutive(...$caughtArguments)
            ->willReturnOnConsecutiveCalls(...array_fill(0, $caughtArgumentCount, true));

        $lexer = new Lexer($handler, "ignore");
        $lexer->addEntryPattern('<file>(?=.*</file>)', "ignore", "caught");
        $lexer->addExitPattern("</file>", "caught");
        $lexer->addSpecialPattern('b','caught','special');
        $lexer->mapHandler('special','caught');
        $lexer->addPattern('c','caught');

        $this->assertTrue($lexer->parse($doc));
    }

    function testIndexLookaheadNotEqual() {
        $doc = "aaa<file>bcd</file>eee";

        $caughtArguments = [
            ["<file>", DOKU_LEXER_ENTER, strpos($doc, '<file>')],
            ["b", DOKU_LEXER_SPECIAL, strpos($doc, 'b')],
            ["c", DOKU_LEXER_MATCHED, strpos($doc, 'c')],
            ["d", DOKU_LEXER_UNMATCHED, strpos($doc, 'd')],
            ["</file>", DOKU_LEXER_EXIT, strpos($doc, '</file>')],
        ];
        $caughtArgumentCount = count($caughtArguments);

        $handler = $this->createMock('TestParserByteIndex');
        $handler->expects($this->any())->method('ignore')->will($this->returnValue(true));
        $handler
            ->expects($this->exactly($caughtArgumentCount))
            ->method('caught')
            ->withConsecutive(...$caughtArguments)
            ->willReturnOnConsecutiveCalls(...array_fill(0, $caughtArgumentCount, true));

        $lexer = new Lexer($handler, "ignore");
        $lexer->addEntryPattern('<file>(?!foo)', "ignore", "caught");
        $lexer->addExitPattern("</file>", "caught");
        $lexer->addSpecialPattern('b','caught','special');
        $lexer->mapHandler('special','caught');
        $lexer->addPattern('c','caught');

        $this->assertTrue($lexer->parse($doc));
    }

    function testIndexLookbehindEqual() {
        $doc = "aaa<file>bcd</file>eee";

        $caughtArguments = [
            ["<file>", DOKU_LEXER_ENTER, strpos($doc, '<file>')],
            ["b", DOKU_LEXER_SPECIAL, strpos($doc, 'b')],
            ["c", DOKU_LEXER_MATCHED, strpos($doc, 'c')],
            ["d", DOKU_LEXER_UNMATCHED, strpos($doc, 'd')],
            ["</file>", DOKU_LEXER_EXIT, strpos($doc, '</file>')],
        ];
        $caughtArgumentCount = count($caughtArguments);

        $handler = $this->createMock('TestParserByteIndex');
        $handler->expects($this->any())->method('ignore')->will($this->returnValue(true));
        $handler
            ->expects($this->exactly($caughtArgumentCount))
            ->method('caught')
            ->withConsecutive(...$caughtArguments)
            ->willReturnOnConsecutiveCalls(...array_fill(0, $caughtArgumentCount, true));

        $lexer = new Lexer($handler, "ignore");
        $lexer->addEntryPattern('<file>', "ignore", "caught");
        $lexer->addExitPattern("(?<=d)</file>", "caught");
        $lexer->addSpecialPattern('b','caught','special');
        $lexer->mapHandler('special','caught');
        $lexer->addPattern('c','caught');

        $this->assertTrue($lexer->parse($doc));
    }

    function testIndexLookbehindNotEqual() {
        $doc = "aaa<file>bcd</file>eee";

        $caughtArguments = [
            ["<file>", DOKU_LEXER_ENTER, strpos($doc, '<file>')],
            ["b", DOKU_LEXER_SPECIAL, strpos($doc, 'b')],
            ["c", DOKU_LEXER_MATCHED, strpos($doc, 'c')],
            ["d", DOKU_LEXER_UNMATCHED, strpos($doc, 'd')],
            ["</file>", DOKU_LEXER_EXIT, strpos($doc, '</file>')],
        ];
        $caughtArgumentCount = count($caughtArguments);

        $handler = $this->createMock('TestParserByteIndex');
        $handler->expects($this->any())->method('ignore')->will($this->returnValue(true));
        $handler
            ->expects($this->exactly($caughtArgumentCount))
            ->method('caught')
            ->withConsecutive(...$caughtArguments)
            ->willReturnOnConsecutiveCalls(...array_fill(0, $caughtArgumentCount, true));

        $lexer = new Lexer($handler, 'ignore');
        $lexer->addEntryPattern('<file>', 'ignore', 'caught');
        $lexer->addExitPattern('(?<!c)</file>', 'caught');
        $lexer->addSpecialPattern('b','caught','special');
        $lexer->mapHandler('special','caught');
        $lexer->addPattern('c','caught');

        $this->assertTrue($lexer->parse($doc));
    }

    /**
     * This test is primarily to ensure the correct match is chosen
     * when there are non-captured elements in the pattern.
     */
    function testIndexSelectCorrectMatch() {
        $doc = "ALL FOOLS ARE FOO";
        $pattern = '\bFOO\b';

        $handler = $this->createMock('TestParserByteIndex');
        $handler->expects($this->any())->method('ignore')->will($this->returnValue(true));

        $matches = [];
        preg_match('/'.$pattern.'/',$doc,$matches,PREG_OFFSET_CAPTURE);

        $handler->expects($this->once())->method('caught')
            ->with("FOO", DOKU_LEXER_SPECIAL, $matches[0][1])->will($this->returnValue(true));

        $lexer = new Lexer($handler, "ignore");
        $lexer->addSpecialPattern($pattern,'ignore','caught');

        $this->assertTrue($lexer->parse($doc));
    }

}
