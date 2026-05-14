<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ModeRegistry;
use dokuwiki\Parsing\ParserMode\GfmBacktickSingle;
use dokuwiki\Parsing\ParserMode\GfmEmphasis;

/**
 * Tests for the GFM inline code-span mode — single-backtick spans.
 */
class GfmBacktickSingleTest extends ParserTestBase
{
    public function setUp(): void
    {
        parent::setUp();
        global $conf;
        $conf['syntax'] = 'md';
        ModeRegistry::reset();
    }

    public function tearDown(): void
    {
        ModeRegistry::reset();
        parent::tearDown();
    }

    function testBasicCodeSpan()
    {
        $this->P->addMode('gfm_backtick_single', new GfmBacktickSingle());
        $this->P->parse('Foo `Bar` Baz');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['monospace_open', []],
            ['unformatted', ['Bar']],
            ['monospace_close', []],
            ['cdata', [' Baz']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testSingleCharacterBody()
    {
        $this->P->addMode('gfm_backtick_single', new GfmBacktickSingle());
        $this->P->parse('foo `b` bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nfoo "]],
            ['monospace_open', []],
            ['unformatted', ['b']],
            ['monospace_close', []],
            ['cdata', [' bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testTwoSeparateSpansOnOneLine()
    {
        $this->P->addMode('gfm_backtick_single', new GfmBacktickSingle());
        $this->P->parse('`one` and `two`');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n"]],
            ['monospace_open', []],
            ['unformatted', ['one']],
            ['monospace_close', []],
            ['cdata', [' and ']],
            ['monospace_open', []],
            ['unformatted', ['two']],
            ['monospace_close', []],
            ['cdata', ['']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testUnmatchedOpenerStaysLiteral()
    {
        $this->P->addMode('gfm_backtick_single', new GfmBacktickSingle());
        $this->P->parse('foo `bar with no closer');
        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains('monospace_open', $modes,
            'Unmatched opening backtick must stay literal');
    }

    function testAsymmetricEdgeSpaceIsPreserved()
    {
        // GFM example 342. Input ` a` — a leading space but no trailing
        // space. Body stays as " a"; strip only fires when BOTH ends are
        // whitespace.
        $this->P->addMode('gfm_backtick_single', new GfmBacktickSingle());
        $this->P->parse('` a`');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n"]],
            ['monospace_open', []],
            ['unformatted', [' a']],
            ['monospace_close', []],
            ['cdata', ['']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testSymmetricEdgeSpaceIsStripped()
    {
        // Body with whitespace on both sides and non-whitespace content
        // in the middle gets one space stripped from each end. Input
        // body is " foo "; after strip it becomes "foo".
        $this->P->addMode('gfm_backtick_single', new GfmBacktickSingle());
        $this->P->parse('` foo `');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n"]],
            ['monospace_open', []],
            ['unformatted', ['foo']],
            ['monospace_close', []],
            ['cdata', ['']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testAllWhitespaceBodyIsPreserved()
    {
        // A body of pure whitespace is a valid code span and kept as is
        // (strip is skipped because trim of the body is empty).
        $this->P->addMode('gfm_backtick_single', new GfmBacktickSingle());
        $this->P->parse('a ` ` b');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\na "]],
            ['monospace_open', []],
            ['unformatted', [' ']],
            ['monospace_close', []],
            ['cdata', [' b']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testEmptyDelimiterDoesNotMatch()
    {
        // Two adjacent backticks with no matching pair later in the
        // paragraph stay literal — the length-boundary guards reject them
        // as an n=1 opener followed immediately by an n=1 closer.
        $this->P->addMode('gfm_backtick_single', new GfmBacktickSingle());
        $this->P->parse('foo `` bar');
        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains('monospace_open', $modes,
            'Bare adjacent backticks with no closer must stay literal');
    }

    function testN1BodyCanContainDoubleBacktickRun()
    {
        // GFM example 340. Input backtick-space-2xbacktick-space-backtick.
        // The interior run of two is not a valid n=1 closer, so it lives
        // in the body; edge-space stripping then leaves just the two
        // backticks as the body content.
        $this->P->addMode('gfm_backtick_single', new GfmBacktickSingle());
        $this->P->parse('` `` `');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n"]],
            ['monospace_open', []],
            ['unformatted', ['``']],
            ['monospace_close', []],
            ['cdata', ['']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testRunOfThreeBackticksIsNotAnN1Span()
    {
        // The length-boundary guards on the opener reject a backtick that
        // is immediately followed by another one, so a run of three or
        // more never opens an n=1 span. Triple-backtick fenced blocks
        // are a separate mode's concern.
        $this->P->addMode('gfm_backtick_single', new GfmBacktickSingle());
        $this->P->parse('foo ```bar``` baz');
        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains('monospace_open', $modes,
            'A run of 3 backticks must not trigger an n=1 span');
    }

    function testDoesNotSpanParagraphBoundary()
    {
        $this->P->addMode('gfm_backtick_single', new GfmBacktickSingle());
        $this->P->parse("This `has a\n\nnew paragraph`.");
        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains('monospace_open', $modes,
            'GfmBacktickSingle must not open when the closer is past a blank line');
    }

    function testAllowsSingleNewline()
    {
        $this->P->addMode('gfm_backtick_single', new GfmBacktickSingle());
        $this->P->parse("`open\nclose`");
        $modes = array_column($this->H->calls, 0);
        $this->assertContains('monospace_open', $modes,
            'GfmBacktickSingle must still match across a single newline');
    }

    function testContentIsLiteral()
    {
        // Other inline modes must not parse inside a code span.
        $this->P->addMode('gfm_emphasis', new GfmEmphasis());
        $this->P->addMode('gfm_backtick_single', new GfmBacktickSingle());
        $this->P->parse('`*foo*`');
        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains('emphasis_open', $modes,
            'Emphasis must not parse inside a code span');
        $this->assertContains('monospace_open', $modes,
            'Backtick span must emit monospace_open');

        // The emphasized text stays as an unformatted (verbatim) call
        // inside the span — same treatment as nowiki and %%.
        $unformatted = array_filter($this->H->calls, static fn($c) => $c[0] === 'unformatted');
        $bodies = array_map(static fn($c) => $c[1][0], $unformatted);
        $this->assertContains('*foo*', $bodies,
            'Raw *foo* must appear as verbatim unformatted content');
    }

    function testSortValue()
    {
        $mode = new GfmBacktickSingle();
        $this->assertSame(165, $mode->getSort());
    }
}
