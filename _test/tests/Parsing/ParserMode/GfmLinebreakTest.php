<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\GfmEmphasis;
use dokuwiki\Parsing\ParserMode\GfmLinebreak;

/**
 * Tests for the GFM hard-line-break mode.
 */
class GfmLinebreakTest extends ParserTestBase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->setSyntax('md');
    }

    function testTwoTrailingSpacesProduceLinebreak()
    {
        $this->P->addMode('gfm_linebreak', new GfmLinebreak());
        $this->P->parse("foo  \nbar");

        $names = array_column($this->H->calls, 0);
        $this->assertContains('linebreak', $names,
            'Two trailing spaces before a newline must produce a linebreak call');
    }

    function testManyTrailingSpacesProduceLinebreak()
    {
        $this->P->addMode('gfm_linebreak', new GfmLinebreak());
        $this->P->parse("foo       \nbar");

        $names = array_column($this->H->calls, 0);
        $this->assertContains('linebreak', $names,
            '7+ trailing spaces before a newline must produce a linebreak call');
    }

    function testBackslashNewlineProducesLinebreak()
    {
        $this->P->addMode('gfm_linebreak', new GfmLinebreak());
        $this->P->parse("foo\\\nbar");

        $names = array_column($this->H->calls, 0);
        $this->assertContains('linebreak', $names,
            'A single backslash before a newline must produce a linebreak call');
    }

    function testLeadingWhitespaceOnNextLineConsumed()
    {
        // Spec example 656 / 657: leading spaces at the beginning of the
        // next line are dropped — the rendered HTML must not carry them.
        $this->P->addMode('gfm_linebreak', new GfmLinebreak());
        $this->P->parse("foo  \n     bar");

        $cdata = array_filter($this->H->calls, static fn($c) => $c[0] === 'cdata');
        $joined = implode('', array_map(static fn($c) => $c[1][0], $cdata));

        $this->assertSame("\nfoobar", $joined,
            'Leading whitespace on the line after a hard break must be consumed');
    }

    function testNoLinebreakAtParagraphBreak()
    {
        // Spec example 665 (analogue): trailing spaces immediately before
        // a paragraph break are not a hard break — the lookahead rejects.
        $this->P->addMode('gfm_linebreak', new GfmLinebreak());
        $this->P->parse("foo  \n\nbar");

        $names = array_column($this->H->calls, 0);
        $this->assertNotContains('linebreak', $names,
            'Trailing spaces before a blank line must not produce a hard break');
    }

    function testNoLinebreakAtEof()
    {
        // Spec example 665: trailing spaces at end of document are not a
        // hard break. The parser appends `\n`, so the lookahead's `\z` arm
        // catches this case.
        $this->P->addMode('gfm_linebreak', new GfmLinebreak());
        $this->P->parse('foo  ');

        $names = array_column($this->H->calls, 0);
        $this->assertNotContains('linebreak', $names,
            'Trailing spaces at EOF must not produce a hard break');
    }

    function testBackslashAtEofStaysLiteral()
    {
        // Spec example 664: a single trailing backslash at end of document
        // is not a hard break — same paragraph-end rule.
        $this->P->addMode('gfm_linebreak', new GfmLinebreak());
        $this->P->parse('foo\\');

        $names = array_column($this->H->calls, 0);
        $this->assertNotContains('linebreak', $names,
            'A trailing backslash at EOF must stay literal, not produce a break');

        $cdata = array_filter($this->H->calls, static fn($c) => $c[0] === 'cdata');
        $joined = implode('', array_map(static fn($c) => $c[1][0], $cdata));
        $this->assertStringContainsString('\\', $joined,
            'The literal backslash must survive in cdata when no break fires');
    }

    function testWorksInsideEmphasis()
    {
        // Spec example 658: hard breaks fire inside inline containers.
        // GfmLinebreak is SUBSTITUTION, GfmEmphasis allows SUBSTITUTION via
        // its allowedModes — so the break appears between the open and
        // close emphasis calls.
        $this->P->addMode('gfm_emphasis', new GfmEmphasis());
        $this->P->addMode('gfm_linebreak', new GfmLinebreak());
        $this->P->parse("*foo  \nbar*");

        $names = array_column($this->H->calls, 0);
        $emOpen = array_search('emphasis_open', $names, true);
        $break  = array_search('linebreak', $names, true);
        $emClose = array_search('emphasis_close', $names, true);

        $this->assertNotFalse($emOpen, 'emphasis_open must fire');
        $this->assertNotFalse($break, 'linebreak must fire inside emphasis');
        $this->assertNotFalse($emClose, 'emphasis_close must fire');
        $this->assertLessThan($break, $emOpen,
            'linebreak must come after the emphasis opener');
        $this->assertLessThan($emClose, $break,
            'linebreak must come before the emphasis closer');
    }

    function testGetSortValue()
    {
        $mode = new GfmLinebreak();
        $this->assertSame(140, $mode->getSort());
    }
}
