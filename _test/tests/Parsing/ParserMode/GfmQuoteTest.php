<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ModeRegistry;
use dokuwiki\Parsing\ParserMode\GfmQuote;

/**
 * Tests for GFM-style block quotes.
 *
 * GfmQuote is the unified blockquote implementation covering both DW and
 * GFM dialects. The mode captures the entire quote via addSpecialPattern
 * and sub-parses the stripped body, so the outer parser only needs
 * gfm_quote attached; inline modes and block modes (lists, code blocks,
 * nested quotes) are picked up by the sub-parser.
 *
 * Two rendering shapes are exercised. Under DW-preferred syntax, a
 * post-pass flattens the sub-parser's paragraph wrapping into linebreak-
 * separated cdata so existing DW pages keep their `<br/>`-between-lines
 * rendering. Under MD-preferred syntax the sub-parser's paragraph
 * wrapping survives — a quote with one paragraph emits
 * `<blockquote><p>...</p></blockquote>`.
 */
class GfmQuoteTest extends ParserTestBase
{
    public function tearDown(): void
    {
        ModeRegistry::reset();
        parent::tearDown();
    }

    private function setSyntax(string $syntax): void
    {
        global $conf;
        $conf['syntax'] = $syntax;
        ModeRegistry::reset();
    }

    /**
     * Recursively flatten call lists, descending into `nest` content.
     * Useful for tests that just check whether an instruction appears
     * somewhere in the rendered output regardless of nesting depth.
     */
    private function flatNames(array $calls): array
    {
        $names = [];
        foreach ($calls as $call) {
            $names[] = $call[0];
            if ($call[0] === 'nest') {
                $names = array_merge($names, $this->flatNames($call[1][0]));
            }
        }
        return $names;
    }

    public function testSortValue()
    {
        $mode = new GfmQuote();
        $this->assertSame(220, $mode->getSort());
    }

    // ----- DW-preferred rendering: linebreak-separated, no <p> ------------

    public function testDwSingleLine()
    {
        $this->setSyntax('dw');
        $this->P->addMode('gfm_quote', new GfmQuote());
        $this->P->parse("> foo\n");

        $expected = [
            ['document_start', []],
            ['quote_open', []],
            ['nest', [[ ['cdata', ['foo']] ]]],
            ['quote_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($expected, $this->H->calls);
    }

    public function testDwSpaceAfterMarkerOptional()
    {
        // GFM allows omitting the space after `>`; DW always did. Strip
        // logic removes one optional space after the `>`, so `>foo` and
        // `> foo` both produce cdata "foo".
        $this->setSyntax('dw');
        $this->P->addMode('gfm_quote', new GfmQuote());
        $this->P->parse(">foo\n");

        $names = $this->flatNames($this->H->calls);
        $this->assertContains('quote_open', $names);
        $this->assertContains('cdata', $names);
    }

    public function testDwTwoLinesEmitLinebreak()
    {
        // The DW-preferred post-pass converts the sub-parser's paragraph
        // wrapping into a linebreak between the two cdata calls, matching
        // the historical `<blockquote>foo<br/>bar</blockquote>` shape.
        $this->setSyntax('dw');
        $this->P->addMode('gfm_quote', new GfmQuote());
        $this->P->parse("> foo\n> bar\n");

        $expected = [
            ['document_start', []],
            ['quote_open', []],
            ['nest', [[
                ['cdata', ['foo']],
                ['linebreak', []],
                ['cdata', ['bar']],
            ]]],
            ['quote_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($expected, $this->H->calls);
    }

    public function testDwBlankMarkerLineEmitsTwoLinebreaks()
    {
        // `>` alone between content lines is a paragraph break in GFM.
        // The DW post-pass replaces each p_open and each p_close with a
        // linebreak, producing two adjacent linebreak calls between the
        // two content cdata — matches the historical DW two-`<br/>` shape.
        $this->setSyntax('dw');
        $this->P->addMode('gfm_quote', new GfmQuote());
        $this->P->parse("> foo\n>\n> bar\n");

        $expected = [
            ['document_start', []],
            ['quote_open', []],
            ['nest', [[
                ['cdata', ['foo']],
                ['linebreak', []],
                ['linebreak', []],
                ['cdata', ['bar']],
            ]]],
            ['quote_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($expected, $this->H->calls);
    }

    public function testDwNested()
    {
        $this->setSyntax('dw');
        $this->P->addMode('gfm_quote', new GfmQuote());
        $this->P->parse("> > foo\n");

        // The outer captures a single line `> > foo`. Stripping the
        // outer marker leaves `> foo`, which the sub-parser feeds back
        // through GfmQuote — recursion produces a nested quote_open /
        // quote_close pair carrying the cdata.
        $names = $this->flatNames($this->H->calls);
        $opens  = array_filter($names, static fn($n) => $n === 'quote_open');
        $closes = array_filter($names, static fn($n) => $n === 'quote_close');
        $this->assertCount(2, $opens, 'two levels of quote_open expected');
        $this->assertCount(2, $closes, 'two levels of quote_close expected');
    }

    public function testDwNoLazyContinuation()
    {
        // GfmQuote does not implement lazy continuation: every quote
        // line must begin with `>`. `bar` without a `>` prefix terminates
        // the quote, so it ends up as a separate paragraph — matching
        // today's DW behavior.
        $this->setSyntax('dw');
        $this->P->addMode('gfm_quote', new GfmQuote());
        $this->P->parse("> foo\nbar\n");

        $opens = array_filter($this->H->calls, static fn($c) => $c[0] === 'quote_open');
        $this->assertCount(1, $opens, 'quote opens once and stops at the non-`>` line');

        // `bar` is outside the quote — find a top-level cdata after the close
        $afterClose = false;
        $sawBarOutside = false;
        foreach ($this->H->calls as $call) {
            if ($call[0] === 'quote_close') $afterClose = true;
            if ($afterClose && $call[0] === 'cdata' && str_contains($call[1][0], 'bar')) {
                $sawBarOutside = true;
            }
        }
        $this->assertTrue($sawBarOutside, '`bar` must appear as cdata outside the quote');
    }

    public function testDwBlankLineSeparatesQuotes()
    {
        // A truly blank line ends the quote. The next `>` starts a new
        // quote, producing two distinct quote_open / quote_close pairs.
        $this->setSyntax('dw');
        $this->P->addMode('gfm_quote', new GfmQuote());
        $this->P->parse("> foo\n\n> bar\n");

        $opens = array_filter($this->H->calls, static fn($c) => $c[0] === 'quote_open');
        $closes = array_filter($this->H->calls, static fn($c) => $c[0] === 'quote_close');
        $this->assertCount(2, $opens, 'two distinct quote blocks');
        $this->assertCount(2, $closes);
    }

    public function testDwHeaderInsideQuoteStaysCdata()
    {
        // Sub-parser excludes BASEONLY (Header / GfmHeader). Header
        // instructions drive section-edit anchors and TOC entries that
        // do not compose with `<blockquote>`. `# Foo` therefore stays
        // as plain cdata text.
        $this->setSyntax('dw');
        $this->P->addMode('gfm_quote', new GfmQuote());
        $this->P->parse("> # Foo\n");

        $names = $this->flatNames($this->H->calls);
        $this->assertNotContains('header', $names);
        $this->assertNotContains('section_open', $names);
        $this->assertContains('cdata', $names);
    }

    // ----- MD-preferred rendering: paragraph wrapping survives ------------

    public function testMdSingleParagraph()
    {
        $this->setSyntax('md');
        $this->P->addMode('gfm_quote', new GfmQuote());
        $this->P->parse("> foo\n> bar\n");

        // Sub-parser wraps the body in `p_open` / `p_close`. The outer
        // wraps them inside a `nest`, and Block treats the nest as
        // opaque. Two `>`-content lines join into one paragraph.
        $expected = [
            ['document_start', []],
            ['quote_open', []],
            ['nest', [[
                ['p_open', []],
                ['cdata', ["foo\nbar"]],
                ['p_close', []],
            ]]],
            ['quote_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($expected, $this->H->calls);
    }

    public function testMdMultiParagraph()
    {
        // `>` alone between content lines creates two paragraphs in one
        // blockquote — under MD-preferred the post-pass does not run, so
        // the sub-parser's `p_open` / `p_close` pairs survive intact.
        $this->setSyntax('md');
        $this->P->addMode('gfm_quote', new GfmQuote());
        $this->P->parse("> foo\n>\n> bar\n");

        $names = $this->flatNames($this->H->calls);
        $pOpens = array_filter($names, static fn($n) => $n === 'p_open');
        $pCloses = array_filter($names, static fn($n) => $n === 'p_close');
        $this->assertCount(2, $pOpens, 'two paragraphs inside one blockquote');
        $this->assertCount(2, $pCloses);
    }

    public function testMdListInsideQuote()
    {
        // GfmListblock is loaded under MD-preferred syntax, so a list
        // inside a quote parses as a real list. The sub-parser's list
        // calls land inside the outer `nest` wrapper.
        $this->setSyntax('md');
        ModeRegistry::reset();
        // Add the registry's full mode set so gfm_listblock is reachable
        // via the sub-parser (the sub-parser uses ModeRegistry::getModes,
        // which honors $conf['syntax']).
        foreach (ModeRegistry::getInstance()->getModes() as $m) {
            $this->P->addMode($m['mode'], $m['obj']);
        }

        $this->P->parse("> - foo\n> - bar\n");

        $names = $this->flatNames($this->H->calls);
        $this->assertContains('quote_open', $names);
        $this->assertContains('listu_open', $names, 'list inside quote must parse');
        $this->assertContains('listu_close', $names);
        $this->assertContains('quote_close', $names);
    }
}
