<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\GfmBacktickSingle;
use dokuwiki\Parsing\ParserMode\GfmEmphasis;
use dokuwiki\Parsing\ParserMode\Monospace;

/**
 * Tests for the GFM asterisk emphasis mode (`*text*`).
 */
class GfmEmphasisTest extends ParserTestBase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->setSyntax('md');
    }

    function testBasicAsterisk()
    {
        $this->P->addMode('gfm_emphasis', new GfmEmphasis());
        $this->P->parse('Foo *Bar* Baz');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['emphasis_open', []],
            ['cdata', ['Bar']],
            ['emphasis_close', []],
            ['cdata', [' Baz']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testSingleCharacter()
    {
        $this->P->addMode('gfm_emphasis', new GfmEmphasis());
        $this->P->parse('foo *b* bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nfoo "]],
            ['emphasis_open', []],
            ['cdata', ['b']],
            ['emphasis_close', []],
            ['cdata', [' bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMultipleWords()
    {
        $this->P->addMode('gfm_emphasis', new GfmEmphasis());
        $this->P->parse('*three four five*');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n"]],
            ['emphasis_open', []],
            ['cdata', ['three four five']],
            ['emphasis_close', []],
            ['cdata', ['']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testTwoSeparateEmphasisOnOneLine()
    {
        $this->P->addMode('gfm_emphasis', new GfmEmphasis());
        $this->P->parse('*one* and *two*');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n"]],
            ['emphasis_open', []],
            ['cdata', ['one']],
            ['emphasis_close', []],
            ['cdata', [' and ']],
            ['emphasis_open', []],
            ['cdata', ['two']],
            ['emphasis_close', []],
            ['cdata', ['']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testUnmatchedOpenerDoesNotEmphasise()
    {
        $this->P->addMode('gfm_emphasis', new GfmEmphasis());
        $this->P->parse('foo *bar with no closer');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nfoo *bar with no closer"]],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testOpenerFollowedBySpaceDoesNotEmphasise()
    {
        $this->P->addMode('gfm_emphasis', new GfmEmphasis());
        $this->P->parse('foo * bar* baz');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nfoo * bar* baz"]],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testEmptyDelimiterDoesNotEmphasise()
    {
        $this->P->addMode('gfm_emphasis', new GfmEmphasis());
        $this->P->parse('foo ** bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nfoo ** bar"]],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testUnderscoreIsNotEmphasised()
    {
        // GfmEmphasis handles `*` only — `_` is reserved to avoid the
        // `__underline__` conflict with DokuWiki's underline syntax;
        // GfmEmphasisUnderscore handles `_` separately when MD-preferred.
        $this->P->addMode('gfm_emphasis', new GfmEmphasis());
        $this->P->parse('foo _bar_ baz');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nfoo _bar_ baz"]],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMultilineEmphasis()
    {
        $this->P->addMode('gfm_emphasis', new GfmEmphasis());
        $this->P->parse("*line\nline\nline*");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n"]],
            ['emphasis_open', []],
            ['cdata', ["line\nline\nline"]],
            ['emphasis_close', []],
            ['cdata', ['']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testModeNameIsDistinctFromInstructionName()
    {
        // The lexer mode is registered as 'gfm_emphasis' (to avoid collision
        // with DW Emphasis), but instructions are 'emphasis_open/close'
        // so the existing XHTML renderer emits <em>.
        $mode = new GfmEmphasis();
        $this->assertSame(80, $mode->getSort());
    }

    function testDoesNotSpanParagraphBoundary()
    {
        // An unclosed `*` followed by a blank line must stay literal — the
        // entry pattern's lookahead is paragraph-boundary-safe.
        $this->P->addMode('gfm_emphasis', new GfmEmphasis());
        $this->P->parse("*open\n\nclose*");
        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains('emphasis_open', $modes,
            'GfmEmphasis must not open when the closing `*` is past a blank line');
    }

    function testAllowsSingleNewline()
    {
        // Single newlines are fine inside emphasis (multi-line emphasis).
        $this->P->addMode('gfm_emphasis', new GfmEmphasis());
        $this->P->parse("*open\nclose*");
        $modes = array_column($this->H->calls, 0);
        $this->assertContains('emphasis_open', $modes,
            'GfmEmphasis must still match across a single newline');
    }

    function testAsteriskDoesNotSpanMonospaceBoundary()
    {
        // A `*` in a glob inside ''…'' must not pair with the `*` of a
        // following ''…'' span: its closer lies past the monospace closer, so
        // it stays literal instead of dragging the monospace boundary along.
        $this->setSyntax('dw+md');
        $this->P->addMode('monospace', new Monospace());
        $this->P->addMode('gfm_emphasis', new GfmEmphasis());
        $this->P->parse("''aaa*.conf'', ''bbb*.conf''");

        $calls = array_map(fn($call) => [$call[0], $call[1]], $this->H->calls);
        $this->assertNotContains(['emphasis_open', []], $calls);
        $this->assertContains(['cdata', ['aaa*.conf']], $calls);
        $this->assertContains(['cdata', ['bbb*.conf']], $calls);
    }

    function testAsteriskStillEmphasisesWithinOneMonospaceSpan()
    {
        // The counterpart: a `*…*` pair fully inside one ''…'' span still
        // renders as emphasis.
        $this->setSyntax('dw+md');
        $this->P->addMode('monospace', new Monospace());
        $this->P->addMode('gfm_emphasis', new GfmEmphasis());
        $this->P->parse("''a*b*c''");

        $calls = array_map(fn($call) => [$call[0], $call[1]], $this->H->calls);
        $this->assertContains(['emphasis_open', []], $calls);
        $this->assertContains(['cdata', ['b']], $calls);
    }

    function testAsteriskAdjacentToMonospaceCloserStaysLiteral()
    {
        // A lone `*` as the entire ''…'' content sits right before the
        // monospace closer; pairing it with a `*` in a later span would
        // drag the monospace boundary along, so it stays literal.
        $this->setSyntax('dw+md');
        $this->P->addMode('monospace', new Monospace());
        $this->P->addMode('gfm_emphasis', new GfmEmphasis());
        $this->P->parse("''*'' as the subdomain name, e.g.: ''*.example.com''");

        $calls = array_map(fn($call) => [$call[0], $call[1]], $this->H->calls);
        $this->assertNotContains(['emphasis_open', []], $calls);
        $this->assertContains(['cdata', ['*']], $calls);
        $this->assertContains(['cdata', ['*.example.com']], $calls);
    }

    function testAsteriskCloserInsideBacktickSpanDoesNotCount()
    {
        // The only closing `*` candidate lives inside a backtick code
        // span whose content is verbatim — the opener can never close, so
        // it stays literal.
        $this->setSyntax('dw+md');
        $this->P->addMode('gfm_emphasis', new GfmEmphasis());
        $this->P->addMode('gfm_backtick_single', new GfmBacktickSingle());
        $this->P->parse('*conf `x*y` end.');

        $calls = array_map(fn($call) => [$call[0], $call[1]], $this->H->calls);
        $this->assertNotContains(['emphasis_open', []], $calls);
        $this->assertContains(['unformatted', ['x*y']], $calls);
    }

    function testAsteriskPairSpanningBacktickSpanStillEmphasises()
    {
        // The counterpart: with a real closer behind the code span, the
        // emphasis wraps the span instead of being rejected because of it.
        $this->setSyntax('dw+md');
        $this->P->addMode('gfm_emphasis', new GfmEmphasis());
        $this->P->addMode('gfm_backtick_single', new GfmBacktickSingle());
        $this->P->parse('*a `b` c*');

        $calls = array_map(fn($call) => [$call[0], $call[1]], $this->H->calls);
        $this->assertContains(['emphasis_open', []], $calls);
        $this->assertContains(['emphasis_close', []], $calls);
        $this->assertContains(['unformatted', ['b']], $calls);
    }
}
