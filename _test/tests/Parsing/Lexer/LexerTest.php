<?php

namespace dokuwiki\test\Parsing\Lexer;

use dokuwiki\Parsing\Lexer\Lexer;

class LexerTest extends \DokuWikiTest
{
    public function testNoPatterns()
    {
        $handler = new RecordingHandler();
        $lexer = new Lexer($handler);
        $this->assertFalse($lexer->parse("abcdef"));
        $this->assertSame([], $handler->recorded);
    }

    public function testEmptyPage()
    {
        $handler = new RecordingHandler();
        $lexer = new Lexer($handler);
        $lexer->addPattern("a+");
        $this->assertTrue($lexer->parse(""));
        $this->assertSame([], $handler->recorded);
    }

    public function testSinglePattern()
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

    public function testMultiplePattern()
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

    public function testIsolatedPattern()
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

    public function testModeChange()
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

    public function testNesting()
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

    public function testSingular()
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

    public function testUnwindTooFar()
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

    public function testModeMapping()
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

    public function testIndex()
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

    public function testIndexLookaheadEqual()
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

    public function testIndexLookaheadNotEqual()
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

    public function testIndexLookbehindEqual()
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

    public function testIndexLookbehindNotEqual()
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
     * Exit-pattern lookbehind must see characters that were already consumed
     * by a preceding token in the same mode.
     *
     * Regression: the Lexer used to hand PCRE a shrinking tail of the subject
     * — once a match was consumed, the bytes before the new cursor were gone
     * and `(?<=X)` assertions silently failed. The Lexer now tracks an offset
     * and passes the full subject to ParallelRegex, so lookbehinds work
     * across token boundaries.
     *
     * Here the exit pattern `(?<=\/>)</x>` requires the `/>` of a self-closing
     * `<a/>` that was consumed as a SPECIAL token on the previous step. Before
     * the fix, `</x>` would fall out as UNMATCHED instead of EXIT.
     */
    public function testIndexLookbehindAcrossConsumedToken()
    {
        $doc = "<x><a/></x>";
        $handler = new RecordingHandler();
        $lexer = new Lexer($handler, 'ignore');
        $lexer->addEntryPattern('<x>', 'ignore', 'caught');
        $lexer->addSpecialPattern('<a\/>', 'caught', 'selfclose');
        $lexer->mapHandler('selfclose', 'caught');
        $lexer->addExitPattern('(?<=\/>)<\/x>', 'caught');
        $this->assertTrue($lexer->parse($doc));

        $caught = array_values(array_filter($handler->recorded, fn($c) => $c[0] === 'caught'));
        $this->assertSame([
            ['caught', '<x>',   \DOKU_LEXER_ENTER,   strpos($doc, '<x>')],
            ['caught', '<a/>',  \DOKU_LEXER_SPECIAL, strpos($doc, '<a/>')],
            ['caught', '</x>',  \DOKU_LEXER_EXIT,    strpos($doc, '</x>')],
        ], $caught);
    }

    /**
     * This test is primarily to ensure the correct match is chosen
     * when there are non-captured elements in the pattern.
     */
    public function testIndexSelectCorrectMatch()
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

    public function testCloserPatternSkipsCandidateWithoutCloserBeforeBoundary()
    {
        // no closer (\w followed by **) before the blank line: the first
        // ** stays literal; the span after the boundary still matches
        $doc = "** foo\n\n**bar**";
        $handler = new RecordingHandler();
        $lexer = new Lexer($handler, 'ignore', true);
        $lexer->addEntryPattern('\*\*', 'ignore', 'caught');
        $lexer->addCloserPattern('\w\*\*', 'caught', '\n\n');
        $lexer->addExitPattern('\*\*', 'caught');

        $this->assertTrue($lexer->parse($doc));
        $caught = array_values(array_filter($handler->recorded, fn($c) => $c[0] === 'caught'));
        $this->assertSame([
            ['caught', '**', \DOKU_LEXER_ENTER, 8],
            ['caught', 'bar', \DOKU_LEXER_UNMATCHED, 10],
            ['caught', '**', \DOKU_LEXER_EXIT, 13],
        ], $caught);
        // the rejected candidate stays part of the unmatched text
        $this->assertSame(['ignore', "** foo\n\n", \DOKU_LEXER_UNMATCHED, 0], $handler->recorded[0]);
    }

    public function testCloserPatternWithoutBoundaryScansWholeSubject()
    {
        // same shape, but with no boundary the closer behind the blank
        // line counts and the first ** does enter the mode
        $doc = "** foo\n\n**bar**";
        $handler = new RecordingHandler();
        $lexer = new Lexer($handler, 'ignore', true);
        $lexer->addEntryPattern('\*\*', 'ignore', 'caught');
        $lexer->addCloserPattern('\w\*\*', 'caught');
        $lexer->addExitPattern('\*\*', 'caught');

        $this->assertTrue($lexer->parse($doc));
        $this->assertSame(['caught', '**', \DOKU_LEXER_ENTER, 0], $handler->recorded[0]);
    }

    public function testCloserPatternRejectsAllCandidates()
    {
        $doc = 'a ** b ** c';
        $handler = new RecordingHandler();
        $lexer = new Lexer($handler, 'ignore', true);
        $lexer->addEntryPattern('\*\*', 'ignore', 'caught');
        $lexer->addCloserPattern('\w\*\*', 'caught');
        $lexer->addExitPattern('\*\*', 'caught');

        $this->assertTrue($lexer->parse($doc));
        $this->assertSame([
            ['ignore', 'a ** b ** c', \DOKU_LEXER_UNMATCHED, 0],
        ], $handler->recorded);
    }

    public function testCloserScanIgnoresCloserInsideSpecialPatternMatch()
    {
        // the only closer candidate sits inside a special pattern's match,
        // which the lexer consumes atomically — the scan must not count it,
        // so the entry is rejected
        $doc = 'a **b %%c** d%% e';
        $handler = new RecordingHandler();
        $lexer = new Lexer($handler, 'ignore', true);
        $lexer->addEntryPattern('\*\*', 'ignore', 'caught');
        $lexer->addCloserPattern('(?<=\w)\*\*', 'caught');
        $lexer->addSpecialPattern('%%.*?%%', 'caught', 'prot');
        $lexer->addExitPattern('\*\*', 'caught');

        $this->assertTrue($lexer->parse($doc));
        $this->assertSame([
            ['ignore', $doc, \DOKU_LEXER_UNMATCHED, 0],
        ], $handler->recorded);
    }

    public function testCloserScanFindsCloserBehindSpecialPatternMatch()
    {
        // a real closer behind the atomically consumed span still
        // validates the entry
        $doc = 'a **b %%c** d%% e** f';
        $handler = new RecordingHandler();
        $lexer = new Lexer($handler, 'ignore', true);
        $lexer->addEntryPattern('\*\*', 'ignore', 'caught');
        $lexer->addCloserPattern('(?<=\w)\*\*', 'caught');
        $lexer->addSpecialPattern('%%.*?%%', 'caught', 'prot');
        $lexer->addExitPattern('\*\*', 'caught');

        $this->assertTrue($lexer->parse($doc));
        $this->assertSame(['caught', '**', \DOKU_LEXER_ENTER, 2], $handler->recorded[1]);
    }

    public function testCloserScanIgnoresCloserInsideVerbatimModeSpan()
    {
        // the only closer candidate sits inside the span of a nested
        // verbatim mode (only exit patterns of its own): the scan derives
        // the span from the entry and exit patterns and skips it
        $doc = 'a **b <v>c** d</v> e';
        $handler = new RecordingHandler();
        $lexer = new Lexer($handler, 'ignore', true);
        $lexer->addEntryPattern('\*\*', 'ignore', 'caught');
        $lexer->addCloserPattern('(?<=\w)\*\*', 'caught');
        $lexer->addEntryPattern('<v>', 'caught', 'verb');
        $lexer->addExitPattern('</v>', 'verb');
        $lexer->addExitPattern('\*\*', 'caught');

        $this->assertTrue($lexer->parse($doc));
        $this->assertSame([
            ['ignore', $doc, \DOKU_LEXER_UNMATCHED, 0],
        ], $handler->recorded);
    }

    public function testCloserMemoIsResetBetweenParses()
    {
        $handler = new RecordingHandler();
        $lexer = new Lexer($handler, 'ignore', true);
        $lexer->addEntryPattern('\*\*', 'ignore', 'caught');
        $lexer->addCloserPattern('\w\*\*', 'caught');
        $lexer->addExitPattern('\*\*', 'caught');

        // first parse memoizes "no closer from position 4 onwards"
        $this->assertTrue($lexer->parse('x ** a'));
        $this->assertSame([
            ['ignore', 'x ** a', \DOKU_LEXER_UNMATCHED, 0],
        ], $handler->recorded);

        // a stale memo would reject the same position in the next subject
        $handler->recorded = [];
        $this->assertTrue($lexer->parse('x **b** c'));
        $this->assertSame(['caught', '**', \DOKU_LEXER_ENTER, 2], $handler->recorded[1]);
    }

    public function testCloserCheckLooksPastUnguardedEnclosingMode()
    {
        // inner // sits in an unguarded middle mode inside a guarded outer
        // mode; its only closer lies beyond the outer closer. The enclosing
        // check must step over the unguarded middle, reach the outer, and
        // reject, so the inner // stays literal instead of pairing across the
        // middle and outer boundaries.
        $doc = '**A ((B //C)) D** E F//G//';
        $handler = new RecordingHandler();
        $lexer = new Lexer($handler, 'ignore', true);
        $lexer->addEntryPattern('\*\*', 'ignore', 'outer');
        $lexer->addCloserPattern('(?<=\w)\*\*', 'outer');
        $lexer->addEntryPattern('\(\(', 'outer', 'middle'); // unguarded: no closer
        $lexer->addExitPattern('\)\)', 'middle');
        $lexer->addEntryPattern('//', 'middle', 'inner');
        $lexer->addCloserPattern('(?<=\w)//', 'inner');
        $lexer->addExitPattern('//', 'inner');
        $lexer->addExitPattern('\*\*', 'outer');

        $this->assertTrue($lexer->parse($doc));
        $enterModes = array_column(
            array_filter($handler->recorded, fn($c) => $c[2] === \DOKU_LEXER_ENTER),
            0
        );
        $this->assertContains('outer', $enterModes);
        $this->assertContains('middle', $enterModes);
        $this->assertNotContains('inner', $enterModes);
    }

    public function testCloserCheckAllowsInnerClosingBeforeGuardedAncestor()
    {
        // same shape, but the inner // now closes before the outer closer, so
        // nesting through the unguarded middle is allowed
        $doc = '**A ((B //C// D)) E**';
        $handler = new RecordingHandler();
        $lexer = new Lexer($handler, 'ignore', true);
        $lexer->addEntryPattern('\*\*', 'ignore', 'outer');
        $lexer->addCloserPattern('(?<=\w)\*\*', 'outer');
        $lexer->addEntryPattern('\(\(', 'outer', 'middle'); // unguarded: no closer
        $lexer->addExitPattern('\)\)', 'middle');
        $lexer->addEntryPattern('//', 'middle', 'inner');
        $lexer->addCloserPattern('(?<=\w)//', 'inner');
        $lexer->addExitPattern('//', 'inner');
        $lexer->addExitPattern('\*\*', 'outer');

        $this->assertTrue($lexer->parse($doc));
        $enterModes = array_column(
            array_filter($handler->recorded, fn($c) => $c[2] === \DOKU_LEXER_ENTER),
            0
        );
        $this->assertContains('inner', $enterModes);
    }
}
