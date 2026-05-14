<?php

namespace dokuwiki\test\Parsing\Lexer;

use dokuwiki\Parsing\Lexer\ParallelRegex;

class ParallelRegexTest extends \DokuWikiTest
{
    function testNoPatterns()
    {
        $regex = new ParallelRegex(false);
        $this->assertFalse($regex->split("Hello", $split));
    }

    function testNoSubject()
    {
        $regex = new ParallelRegex(false);
        $regex->addPattern(".*");
        $this->assertTrue($regex->split("", $split));
        $this->assertEquals("", $split[1]);
    }

    function testMatchAll()
    {
        $regex = new ParallelRegex(false);
        $regex->addPattern(".*");
        $this->assertTrue($regex->split("Hello", $split));
        $this->assertEquals("Hello", $split[1]);
    }

    function testCaseSensitive()
    {
        $regex = new ParallelRegex(true);
        $regex->addPattern("abc");
        $this->assertTrue($regex->split("abcdef", $split));
        $this->assertEquals("abc", $split[1]);
        $regex = new ParallelRegex(true);
        $regex->addPattern("abc");
        $this->assertTrue($regex->split("AAABCabcdef", $split));
        $this->assertEquals("abc", $split[1]);
    }

    function testCaseInsensitive()
    {
        $regex = new ParallelRegex(false);
        $regex->addPattern("abc");
        $this->assertTrue($regex->split("abcdef", $split));
        $this->assertEquals("abc", $split[1]);
        $regex = new ParallelRegex(false);
        $regex->addPattern("abc");
        $this->assertTrue($regex->split("AAABCabcdef", $split));
        $this->assertEquals("ABC", $split[1]);
    }

    function testMatchMultiple()
    {
        $regex = new ParallelRegex(true);
        $regex->addPattern("abc");
        $regex->addPattern("ABC");
        $this->assertTrue($regex->split("abcdef", $split));
        $this->assertEquals("abc", $split[1]);
        $this->assertTrue($regex->split("AAABCabcdef", $split));
        $this->assertEquals("ABC", $split[1]);
        $this->assertFalse($regex->split("Hello", $split));
    }

    function testPatternLabels()
    {
        $regex = new ParallelRegex(false);
        $regex->addPattern("abc", "letter");
        $regex->addPattern("123", "number");
        $this->assertEquals("letter", $regex->split("abcdef", $split));
        $this->assertEquals("abc", $split[1]);
        $this->assertEquals("number", $regex->split("0123456789", $split));
        $this->assertEquals("123", $split[1]);
    }

    function testMatchMultipleWithLookaheadNot()
    {
        $regex = new ParallelRegex(true);
        $regex->addPattern("abc");
        $regex->addPattern("ABC");
        $regex->addPattern("a(?!\n).{1}");
        $this->assertTrue($regex->split("abcdef", $split));
        $this->assertEquals("abc", $split[1]);
        $this->assertTrue($regex->split("AAABCabcdef", $split));
        $this->assertEquals("ABC", $split[1]);
        $this->assertTrue($regex->split("a\nab", $split));
        $this->assertEquals("ab", $split[1]);
        $this->assertFalse($regex->split("Hello", $split));
    }

    function testMatchSetOptionCaseless()
    {
        $regex = new ParallelRegex(true);
        $regex->addPattern("a(?i)b(?i)c");
        $this->assertTrue($regex->split("aBc", $split));
        $this->assertEquals("aBc", $split[1]);
    }

    function testMatchSetOptionUngreedy()
    {
        $regex = new ParallelRegex(true);
        $regex->addPattern("(?U)\w+");
        $this->assertTrue($regex->split("aaaaaa", $split));
        $this->assertEquals("a", $split[1]);
    }

    function testMatchLookaheadEqual()
    {
        $regex = new ParallelRegex(true);
        $regex->addPattern("\w(?=c)");
        $this->assertTrue($regex->split("xbyczd", $split));
        $this->assertEquals("y", $split[1]);
    }

    function testMatchLookaheadNot()
    {
        $regex = new ParallelRegex(true);
        $regex->addPattern("\w(?!b|c)");
        $this->assertTrue($regex->split("xbyczd", $split));
        $this->assertEquals("b", $split[1]);
    }

    function testMatchLookbehindEqual()
    {
        $regex = new ParallelRegex(true);
        $regex->addPattern("(?<=c)\w");
        $this->assertTrue($regex->split("xbyczd", $split));
        $this->assertEquals("z", $split[1]);
    }

    function testMatchLookbehindNot()
    {
        $regex = new ParallelRegex(true);
        $regex->addPattern("(?<!\A|x|b)\w");
        $this->assertTrue($regex->split("xbyczd", $split));
        $this->assertEquals("c", $split[1]);
    }

    function testSplitReturnsPreAndPostMatch()
    {
        $regex = new ParallelRegex(true);
        $regex->addPattern("abc");
        $this->assertTrue($regex->split("xxxabcyyy", $split));
        $this->assertEquals("xxx", $split[0]);
        $this->assertEquals("abc", $split[1]);
        $this->assertEquals("yyy", $split[2]);
    }

    function testSplitWithOffsetSeesLookbehindBeforeOffset()
    {
        // Regression: inline-formatting closers like `(?<=[^\s])\*\*` must
        // see the preceding non-whitespace character even when it was part
        // of a previously matched token (e.g. the `]` of a `[[link]]`).
        // With `$offset`, the full subject is passed to PCRE and the
        // lookbehind works against bytes before the offset.
        $regex = new ParallelRegex(true);
        $regex->addPattern('(?<=[^\s])\*\*');

        // Without offset: subject starts with `**`, lookbehind fails.
        $this->assertFalse((bool) $regex->split("** bar", $split));

        // With offset: full subject passed, lookbehind at offset 10 sees `]`.
        $this->assertTrue((bool) $regex->split("**[[link]]** bar", $split, 10));
        $this->assertEquals("", $split[0]);
        $this->assertEquals("**", $split[1]);
    }

    function testSplitWithOffsetPreIsBetweenOffsetAndMatch()
    {
        $regex = new ParallelRegex(true);
        $regex->addPattern("abc");
        // Match at position 6, offset at 3 — pre is "yyy" (bytes 3..5).
        $this->assertTrue((bool) $regex->split("xxxyyyabczzz", $split, 3));
        $this->assertEquals("yyy", $split[0]);
        $this->assertEquals("abc", $split[1]);
        $this->assertEquals("zzz", $split[2]);
    }
}
