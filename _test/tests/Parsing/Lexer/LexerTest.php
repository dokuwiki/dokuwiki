<?php

namespace dokuwiki\test\Parsing\Lexer;

use dokuwiki\Parsing\Lexer\Lexer;

class LexerTest extends \DokuWikiTest
{
    function testNoPatterns()
    {
        $handler = new RecordingHandler();
        $lexer = new Lexer($handler);
        $this->assertFalse($lexer->parse("abcdef"));
        $this->assertSame([], $handler->recorded);
    }

    function testEmptyPage()
    {
        $handler = new RecordingHandler();
        $lexer = new Lexer($handler);
        $lexer->addPattern("a+");
        $this->assertTrue($lexer->parse(""));
        $this->assertSame([], $handler->recorded);
    }

    function testSinglePattern()
    {
        $handler = new RecordingHandler();
        $lexer = new Lexer($handler);
        $lexer->addPattern("a+");
        $this->assertTrue($lexer->parse("aaaxayyyaxaaaz"));
        $this->assertSame([
            ['accept', 'aaa', \DOKU_LEXER_MATCHED, 0],
            ['accept', 'x', \DOKU_LEXER_UNMATCHED, 3],
            ['accept', 'a', \DOKU_LEXER_MATCHED, 4],
            ['accept', 'yyy', \DOKU_LEXER_UNMATCHED, 5],
            ['accept', 'a', \DOKU_LEXER_MATCHED, 8],
            ['accept', 'x', \DOKU_LEXER_UNMATCHED, 9],
            ['accept', 'aaa', \DOKU_LEXER_MATCHED, 10],
            ['accept', 'z', \DOKU_LEXER_UNMATCHED, 13],
        ], $handler->recorded);
    }

    function testMultiplePattern()
    {
        $handler = new RecordingHandler();
        $lexer = new Lexer($handler);
        $lexer->addPattern("a+");
        $lexer->addPattern("b+");
        $this->assertTrue($lexer->parse("ababbxbaxxxxxxax"));
        $expected = ['a', 'b', 'a', 'bb', 'x', 'b', 'a', 'xxxxxx', 'a', 'x'];
        $actual = array_column($handler->recorded, 1);
        $this->assertSame($expected, $actual);
    }

    function testIsolatedPattern()
    {
        $handler = new RecordingHandler();
        $lexer = new Lexer($handler, "a");
        $lexer->addPattern("a+", "a");
        $lexer->addPattern("b+", "b");
        $this->assertTrue($lexer->parse("abaabxbaaaxaaaax"));
        $this->assertSame([
            ['a', 'a', \DOKU_LEXER_MATCHED, 0],
            ['a', 'b', \DOKU_LEXER_UNMATCHED, 1],
            ['a', 'aa', \DOKU_LEXER_MATCHED, 2],
            ['a', 'bxb', \DOKU_LEXER_UNMATCHED, 4],
            ['a', 'aaa', \DOKU_LEXER_MATCHED, 7],
            ['a', 'x', \DOKU_LEXER_UNMATCHED, 10],
            ['a', 'aaaa', \DOKU_LEXER_MATCHED, 11],
            ['a', 'x', \DOKU_LEXER_UNMATCHED, 15],
        ], $handler->recorded);
    }

    function testModeChange()
    {
        $handler = new RecordingHandler();
        $lexer = new Lexer($handler, "a");
        $lexer->addPattern("a+", "a");
        $lexer->addEntryPattern(":", "a", "b");
        $lexer->addPattern("b+", "b");
        $this->assertTrue($lexer->parse("abaabaaa:ababbabbba"));
        $this->assertSame([
            ['a', 'a', \DOKU_LEXER_MATCHED, 0],
            ['a', 'b', \DOKU_LEXER_UNMATCHED, 1],
            ['a', 'aa', \DOKU_LEXER_MATCHED, 2],
            ['a', 'b', \DOKU_LEXER_UNMATCHED, 4],
            ['a', 'aaa', \DOKU_LEXER_MATCHED, 5],
            ['b', ':', \DOKU_LEXER_ENTER, 8],
            ['b', 'a', \DOKU_LEXER_UNMATCHED, 9],
            ['b', 'b', \DOKU_LEXER_MATCHED, 10],
            ['b', 'a', \DOKU_LEXER_UNMATCHED, 11],
            ['b', 'bb', \DOKU_LEXER_MATCHED, 12],
            ['b', 'a', \DOKU_LEXER_UNMATCHED, 14],
            ['b', 'bbb', \DOKU_LEXER_MATCHED, 15],
            ['b', 'a', \DOKU_LEXER_UNMATCHED, 18],
        ], $handler->recorded);
    }

    function testNesting()
    {
        $handler = new RecordingHandler();
        $lexer = new Lexer($handler, "a");
        $lexer->addPattern("a+", "a");
        $lexer->addEntryPattern("(", "a", "b");
        $lexer->addPattern("b+", "b");
        $lexer->addExitPattern(")", "b");
        $this->assertTrue($lexer->parse("aabaab(bbabb)aab"));
        $this->assertSame([
            ['a', 'aa', \DOKU_LEXER_MATCHED, 0],
            ['a', 'b', \DOKU_LEXER_UNMATCHED, 2],
            ['a', 'aa', \DOKU_LEXER_MATCHED, 3],
            ['a', 'b', \DOKU_LEXER_UNMATCHED, 5],
            ['b', '(', \DOKU_LEXER_ENTER, 6],
            ['b', 'bb', \DOKU_LEXER_MATCHED, 7],
            ['b', 'a', \DOKU_LEXER_UNMATCHED, 9],
            ['b', 'bb', \DOKU_LEXER_MATCHED, 10],
            ['b', ')', \DOKU_LEXER_EXIT, 12],
            ['a', 'aa', \DOKU_LEXER_MATCHED, 13],
            ['a', 'b', \DOKU_LEXER_UNMATCHED, 15],
        ], $handler->recorded);
    }

    function testSingular()
    {
        $handler = new RecordingHandler();
        $lexer = new Lexer($handler, "a");
        $lexer->addPattern("a+", "a");
        $lexer->addSpecialPattern("b+", "a", "b");
        $this->assertTrue($lexer->parse("aabaaxxbbbxx"));
        $this->assertSame([
            ['a', 'aa', \DOKU_LEXER_MATCHED, 0],
            ['b', 'b', \DOKU_LEXER_SPECIAL, 2],
            ['a', 'aa', \DOKU_LEXER_MATCHED, 3],
            ['a', 'xx', \DOKU_LEXER_UNMATCHED, 5],
            ['b', 'bbb', \DOKU_LEXER_SPECIAL, 7],
            ['a', 'xx', \DOKU_LEXER_UNMATCHED, 10],
        ], $handler->recorded);
    }

    function testUnwindTooFar()
    {
        $handler = new RecordingHandler();
        $lexer = new Lexer($handler, "a");
        $lexer->addPattern("a+", "a");
        $lexer->addExitPattern(")", "a");
        $this->assertFalse($lexer->parse("aa)aa"));
        $this->assertSame([
            ['a', 'aa', \DOKU_LEXER_MATCHED, 0],
            ['a', ')', \DOKU_LEXER_EXIT, 2],
        ], $handler->recorded);
    }

    function testModeMapping()
    {
        $handler = new RecordingHandler();
        $lexer = new Lexer($handler, "mode_a");
        $lexer->addPattern("a+", "mode_a");
        $lexer->addEntryPattern("(", "mode_a", "mode_b");
        $lexer->addPattern("b+", "mode_b");
        $lexer->addExitPattern(")", "mode_b");
        $lexer->mapHandler("mode_a", "a");
        $lexer->mapHandler("mode_b", "a");
        $this->assertTrue($lexer->parse("aa(bbabb)b"));
        $this->assertSame([
            ['a', 'aa', \DOKU_LEXER_MATCHED, 0],
            ['a', '(', \DOKU_LEXER_ENTER, 2],
            ['a', 'bb', \DOKU_LEXER_MATCHED, 3],
            ['a', 'a', \DOKU_LEXER_UNMATCHED, 5],
            ['a', 'bb', \DOKU_LEXER_MATCHED, 6],
            ['a', ')', \DOKU_LEXER_EXIT, 8],
            ['a', 'b', \DOKU_LEXER_UNMATCHED, 9],
        ], $handler->recorded);
    }

    function testIndex()
    {
        $doc = "aaa<file>bcd</file>eee";
        $handler = new RecordingHandler();
        $lexer = new Lexer($handler, "ignore");
        $lexer->addEntryPattern("<file>", "ignore", "caught");
        $lexer->addExitPattern("</file>", "caught");
        $lexer->addSpecialPattern('b', 'caught', 'special');
        $lexer->mapHandler('special', 'caught');
        $lexer->addPattern('c', 'caught');
        $this->assertTrue($lexer->parse($doc));

        $caught = array_values(array_filter($handler->recorded, fn($c) => $c[0] === 'caught'));
        $this->assertSame([
            ['caught', '<file>', \DOKU_LEXER_ENTER, strpos($doc, '<file>')],
            ['caught', 'b', \DOKU_LEXER_SPECIAL, strpos($doc, 'b')],
            ['caught', 'c', \DOKU_LEXER_MATCHED, strpos($doc, 'c')],
            ['caught', 'd', \DOKU_LEXER_UNMATCHED, strpos($doc, 'd')],
            ['caught', '</file>', \DOKU_LEXER_EXIT, strpos($doc, '</file>')],
        ], $caught);
    }

    function testIndexLookaheadEqual()
    {
        $doc = "aaa<file>bcd</file>eee";
        $handler = new RecordingHandler();
        $lexer = new Lexer($handler, "ignore");
        $lexer->addEntryPattern('<file>(?=.*</file>)', "ignore", "caught");
        $lexer->addExitPattern("</file>", "caught");
        $lexer->addSpecialPattern('b', 'caught', 'special');
        $lexer->mapHandler('special', 'caught');
        $lexer->addPattern('c', 'caught');
        $this->assertTrue($lexer->parse($doc));

        $caught = array_values(array_filter($handler->recorded, fn($c) => $c[0] === 'caught'));
        $this->assertSame([
            ['caught', '<file>', \DOKU_LEXER_ENTER, strpos($doc, '<file>')],
            ['caught', 'b', \DOKU_LEXER_SPECIAL, strpos($doc, 'b')],
            ['caught', 'c', \DOKU_LEXER_MATCHED, strpos($doc, 'c')],
            ['caught', 'd', \DOKU_LEXER_UNMATCHED, strpos($doc, 'd')],
            ['caught', '</file>', \DOKU_LEXER_EXIT, strpos($doc, '</file>')],
        ], $caught);
    }

    function testIndexLookaheadNotEqual()
    {
        $doc = "aaa<file>bcd</file>eee";
        $handler = new RecordingHandler();
        $lexer = new Lexer($handler, "ignore");
        $lexer->addEntryPattern('<file>(?!foo)', "ignore", "caught");
        $lexer->addExitPattern("</file>", "caught");
        $lexer->addSpecialPattern('b', 'caught', 'special');
        $lexer->mapHandler('special', 'caught');
        $lexer->addPattern('c', 'caught');
        $this->assertTrue($lexer->parse($doc));

        $caught = array_values(array_filter($handler->recorded, fn($c) => $c[0] === 'caught'));
        $this->assertSame([
            ['caught', '<file>', \DOKU_LEXER_ENTER, strpos($doc, '<file>')],
            ['caught', 'b', \DOKU_LEXER_SPECIAL, strpos($doc, 'b')],
            ['caught', 'c', \DOKU_LEXER_MATCHED, strpos($doc, 'c')],
            ['caught', 'd', \DOKU_LEXER_UNMATCHED, strpos($doc, 'd')],
            ['caught', '</file>', \DOKU_LEXER_EXIT, strpos($doc, '</file>')],
        ], $caught);
    }

    function testIndexLookbehindEqual()
    {
        $doc = "aaa<file>bcd</file>eee";
        $handler = new RecordingHandler();
        $lexer = new Lexer($handler, "ignore");
        $lexer->addEntryPattern('<file>', "ignore", "caught");
        $lexer->addExitPattern("(?<=d)</file>", "caught");
        $lexer->addSpecialPattern('b', 'caught', 'special');
        $lexer->mapHandler('special', 'caught');
        $lexer->addPattern('c', 'caught');
        $this->assertTrue($lexer->parse($doc));

        $caught = array_values(array_filter($handler->recorded, fn($c) => $c[0] === 'caught'));
        $this->assertSame([
            ['caught', '<file>', \DOKU_LEXER_ENTER, strpos($doc, '<file>')],
            ['caught', 'b', \DOKU_LEXER_SPECIAL, strpos($doc, 'b')],
            ['caught', 'c', \DOKU_LEXER_MATCHED, strpos($doc, 'c')],
            ['caught', 'd', \DOKU_LEXER_UNMATCHED, strpos($doc, 'd')],
            ['caught', '</file>', \DOKU_LEXER_EXIT, strpos($doc, '</file>')],
        ], $caught);
    }

    function testIndexLookbehindNotEqual()
    {
        $doc = "aaa<file>bcd</file>eee";
        $handler = new RecordingHandler();
        $lexer = new Lexer($handler, 'ignore');
        $lexer->addEntryPattern('<file>', 'ignore', 'caught');
        $lexer->addExitPattern('(?<!c)</file>', 'caught');
        $lexer->addSpecialPattern('b', 'caught', 'special');
        $lexer->mapHandler('special', 'caught');
        $lexer->addPattern('c', 'caught');
        $this->assertTrue($lexer->parse($doc));

        $caught = array_values(array_filter($handler->recorded, fn($c) => $c[0] === 'caught'));
        $this->assertSame([
            ['caught', '<file>', \DOKU_LEXER_ENTER, strpos($doc, '<file>')],
            ['caught', 'b', \DOKU_LEXER_SPECIAL, strpos($doc, 'b')],
            ['caught', 'c', \DOKU_LEXER_MATCHED, strpos($doc, 'c')],
            ['caught', 'd', \DOKU_LEXER_UNMATCHED, strpos($doc, 'd')],
            ['caught', '</file>', \DOKU_LEXER_EXIT, strpos($doc, '</file>')],
        ], $caught);
    }

    /**
     * This test is primarily to ensure the correct match is chosen
     * when there are non-captured elements in the pattern.
     */
    function testIndexSelectCorrectMatch()
    {
        $doc = "ALL FOOLS ARE FOO";
        $pattern = '\bFOO\b';
        $handler = new RecordingHandler();
        $lexer = new Lexer($handler, "ignore");
        $lexer->addSpecialPattern($pattern, 'ignore', 'caught');
        $this->assertTrue($lexer->parse($doc));

        $caught = array_values(array_filter($handler->recorded, fn($c) => $c[0] === 'caught'));
        $matches = [];
        preg_match('/' . $pattern . '/', $doc, $matches, PREG_OFFSET_CAPTURE);
        $this->assertCount(1, $caught);
        $this->assertSame('FOO', $caught[0][1]);
        $this->assertSame(\DOKU_LEXER_SPECIAL, $caught[0][2]);
        $this->assertSame($matches[0][1], $caught[0][3]);
    }
}
