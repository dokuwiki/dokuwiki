<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\Eol;
use dokuwiki\Parsing\ParserMode\GfmHeader;

/**
 * Tests for GFM ATX headings (`# text` through `###### text`).
 */
class GfmHeaderTest extends ParserTestBase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->setSyntax('md');
    }

    function testLevelOne()
    {
        $this->P->addMode('gfm_header', new GfmHeader());
        $this->P->parse("abc\n# Header\ndef");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nabc"]],
            ['p_close', []],
            ['header', ['Header', 1, 4]],
            ['section_open', [1]],
            ['p_open', []],
            ['cdata', ["\ndef"]],
            ['p_close', []],
            ['section_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testAllLevels()
    {
        foreach ([1, 2, 3, 4, 5, 6] as $level) {
            $this->setUp();
            $this->P->addMode('gfm_header', new GfmHeader());
            $marker = str_repeat('#', $level);
            $this->P->parse("$marker foo");
            $calls = array_column($this->H->calls, 0);
            $this->assertContains('header', $calls, "level $level must emit header");

            $headerCall = array_values(array_filter(
                $this->H->calls,
                static fn($c) => $c[0] === 'header'
            ))[0];
            $this->assertSame('foo', $headerCall[1][0], "level $level title");
            $this->assertSame($level, $headerCall[1][1], "level $level level");
        }
    }

    function testSevenHashesIsNotAHeading()
    {
        $this->P->addMode('gfm_header', new GfmHeader());
        $this->P->parse('####### foo');
        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains('header', $modes,
            'A run of 7 `#` must not open an ATX heading');
    }

    function testHashTouchingTextIsNotAHeading()
    {
        $this->P->addMode('gfm_header', new GfmHeader());
        $this->P->parse("#5 bolt\n\n#hashtag");
        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains('header', $modes,
            'A `#` directly followed by a non-space char must not open a heading');
    }

    function testEmptyHeading()
    {
        $this->P->addMode('gfm_header', new GfmHeader());
        $this->P->parse("#\n");
        $headerCalls = array_filter($this->H->calls, static fn($c) => $c[0] === 'header');
        $this->assertCount(1, $headerCalls, 'bare `#` must still emit a heading');
        $call = array_values($headerCalls)[0];
        $this->assertSame('', $call[1][0]);
        $this->assertSame(1, $call[1][1]);
    }

    function testEmptyHeadingWithTrailingSpace()
    {
        $this->P->addMode('gfm_header', new GfmHeader());
        $this->P->parse("## \n");
        $headerCalls = array_filter($this->H->calls, static fn($c) => $c[0] === 'header');
        $call = array_values($headerCalls)[0];
        $this->assertSame('', $call[1][0]);
        $this->assertSame(2, $call[1][1]);
    }

    function testEmptyHeadingWithClosingHashes()
    {
        $this->P->addMode('gfm_header', new GfmHeader());
        $this->P->parse("### ###\n");
        $headerCalls = array_filter($this->H->calls, static fn($c) => $c[0] === 'header');
        $call = array_values($headerCalls)[0];
        $this->assertSame('', $call[1][0]);
        $this->assertSame(3, $call[1][1]);
    }

    function testOptionalClosingHashesStripped()
    {
        $this->P->addMode('gfm_header', new GfmHeader());
        $this->P->parse("## foo ##\n");
        $headerCalls = array_filter($this->H->calls, static fn($c) => $c[0] === 'header');
        $call = array_values($headerCalls)[0];
        $this->assertSame('foo', $call[1][0]);
        $this->assertSame(2, $call[1][1]);
    }

    function testClosingNeedNotMatchOpeningLength()
    {
        $this->P->addMode('gfm_header', new GfmHeader());
        $this->P->parse("# foo ##################################\n");
        $headerCalls = array_filter($this->H->calls, static fn($c) => $c[0] === 'header');
        $call = array_values($headerCalls)[0];
        $this->assertSame('foo', $call[1][0]);
        $this->assertSame(1, $call[1][1]);
    }

    function testTrailingSpacesAfterClosing()
    {
        $this->P->addMode('gfm_header', new GfmHeader());
        $this->P->parse("### foo ###     \n");
        $headerCalls = array_filter($this->H->calls, static fn($c) => $c[0] === 'header');
        $call = array_values($headerCalls)[0];
        $this->assertSame('foo', $call[1][0]);
        $this->assertSame(3, $call[1][1]);
    }

    function testClosingRunFollowedByTextIsNotClosing()
    {
        $this->P->addMode('gfm_header', new GfmHeader());
        $this->P->parse("### foo ### b\n");
        $headerCalls = array_filter($this->H->calls, static fn($c) => $c[0] === 'header');
        $call = array_values($headerCalls)[0];
        $this->assertSame('foo ### b', $call[1][0]);
        $this->assertSame(3, $call[1][1]);
    }

    function testClosingHashMustBePrecededBySpace()
    {
        $this->P->addMode('gfm_header', new GfmHeader());
        $this->P->parse("# foo#\n");
        $headerCalls = array_filter($this->H->calls, static fn($c) => $c[0] === 'header');
        $call = array_values($headerCalls)[0];
        $this->assertSame('foo#', $call[1][0]);
        $this->assertSame(1, $call[1][1]);
    }

    function testIndentedHashIsNotAHeading()
    {
        // GFM tolerates 0-3 spaces of indent; we do not. Any leading
        // whitespace makes the line a paragraph (or preformatted, if
        // it meets that mode's rules).
        foreach ([1, 2, 3] as $indent) {
            $this->setUp();
            $this->P->addMode('gfm_header', new GfmHeader());
            $this->P->parse(str_repeat(' ', $indent) . '### foo');
            $modes = array_column($this->H->calls, 0);
            $this->assertNotContains('header', $modes,
                "indent=$indent must NOT open a heading");
        }
    }

    function testContentInlineWhitespaceCollapsed()
    {
        $this->P->addMode('gfm_header', new GfmHeader());
        $this->P->parse("#                  foo                     \n");
        $headerCalls = array_filter($this->H->calls, static fn($c) => $c[0] === 'header');
        $call = array_values($headerCalls)[0];
        $this->assertSame('foo', $call[1][0]);
    }

    function testHeadingCanInterruptParagraph()
    {
        $this->P->addMode('gfm_header', new GfmHeader());
        $this->P->addMode('eol', new Eol());
        $this->P->parse("Foo bar\n# baz\nBar foo");
        $modes = array_column($this->H->calls, 0);
        $this->assertContains('header', $modes,
            'ATX headings must interrupt paragraphs without requiring a blank line');
    }

    function testSortValue()
    {
        $mode = new GfmHeader();
        $this->assertSame(50, $mode->getSort());
    }
}
