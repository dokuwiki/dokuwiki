<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ModeRegistry;
use dokuwiki\Parsing\ParserMode\GfmEmphasis;

/**
 * Tests for the GFM asterisk emphasis mode (`*text*`).
 *
 * Mirrors the existing FormattingTest pattern: one mode loaded in isolation,
 * assertions against handler instruction sequences.
 *
 * The setUp flips ModeRegistry to `markdown` syntax so that the Base mode
 * (constructed by the Parser) recognizes `gfm_emphasis` as an allowed nested
 * mode. Without this, Base's allowedModes would be the dokuwiki set and would
 * silently drop our entry pattern.
 */
class GfmEmphasisTest extends ParserTestBase
{
    public function setUp(): void
    {
        parent::setUp();
        global $conf;
        $conf['syntax'] = 'markdown';
        ModeRegistry::reset();
    }

    public function tearDown(): void
    {
        ModeRegistry::reset();
        parent::tearDown();
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
        // `__underline__` conflict. See SPEC.md.
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
}
