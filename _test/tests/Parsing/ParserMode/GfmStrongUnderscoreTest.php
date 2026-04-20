<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ModeRegistry;
use dokuwiki\Parsing\ParserMode\GfmStrongUnderscore;

/**
 * Tests for GFM strong emphasis via double underscores (`__text__`).
 *
 * Only loaded when Markdown is the only or preferred syntax. Combines:
 *   - intraword word-boundary rule (multibyte-safe)
 *   - flanking-whitespace rule (no leading/trailing space inside delimiters)
 *   - paragraph-boundary rule (no crossing blank lines)
 */
class GfmStrongUnderscoreTest extends ParserTestBase
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

    function testBasic()
    {
        $this->P->addMode('gfm_strong_underscore', new GfmStrongUnderscore());
        $this->P->parse('Foo __Bar__ Baz');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['strong_open', []],
            ['cdata', ['Bar']],
            ['strong_close', []],
            ['cdata', [' Baz']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testSingleCharacter()
    {
        $this->P->addMode('gfm_strong_underscore', new GfmStrongUnderscore());
        $this->P->parse('__x__');
        $modes = array_column($this->H->calls, 0);
        $this->assertContains('strong_open', $modes);
        $this->assertContains('strong_close', $modes);
    }

    function testMultipleWords()
    {
        $this->P->addMode('gfm_strong_underscore', new GfmStrongUnderscore());
        $this->P->parse('__one two three__');
        $modes = array_column($this->H->calls, 0);
        $this->assertContains('strong_open', $modes);
    }

    function testIntrawordDoesNotOpen()
    {
        // `foo__bar__` — opening `__` intraword (preceded by `o`).
        $this->P->addMode('gfm_strong_underscore', new GfmStrongUnderscore());
        $this->P->parse('foo__bar__');
        $this->assertNotContains('strong_open', array_column($this->H->calls, 0));
    }

    function testLeadingWhitespaceDoesNotOpen()
    {
        $this->P->addMode('gfm_strong_underscore', new GfmStrongUnderscore());
        $this->P->parse('__ foo bar__');
        $this->assertNotContains('strong_open', array_column($this->H->calls, 0));
    }

    function testTrailingWhitespaceDoesNotClose()
    {
        $this->P->addMode('gfm_strong_underscore', new GfmStrongUnderscore());
        $this->P->parse('__foo bar __');
        $this->assertNotContains('strong_open', array_column($this->H->calls, 0));
    }

    function testEmptyDelimiterDoesNotMatch()
    {
        $this->P->addMode('gfm_strong_underscore', new GfmStrongUnderscore());
        $this->P->parse('____');
        $this->assertNotContains('strong_open', array_column($this->H->calls, 0));
    }

    function testMultibyteIntrawordDoesNotMatch()
    {
        // Cyrillic spec example: `пристаням__стремятся__` — intraword, literal.
        $this->P->addMode('gfm_strong_underscore', new GfmStrongUnderscore());
        $this->P->parse('пристаням__стремятся__');
        $this->assertNotContains('strong_open', array_column($this->H->calls, 0));
    }

    function testMultibyteContentInsideStrongWorks()
    {
        $this->P->addMode('gfm_strong_underscore', new GfmStrongUnderscore());
        $this->P->parse('foo __für etwas__ bar');
        $this->assertContains('strong_open', array_column($this->H->calls, 0));
        $this->assertContains('strong_close', array_column($this->H->calls, 0));
    }

    function testDoesNotSpanParagraphBoundary()
    {
        $this->P->addMode('gfm_strong_underscore', new GfmStrongUnderscore());
        $this->P->parse("__open\n\nclose__");
        $this->assertNotContains('strong_open', array_column($this->H->calls, 0));
    }

    function testSortValue()
    {
        $this->assertSame(70, (new GfmStrongUnderscore())->getSort());
    }

    function testInstructionNameIsStrong()
    {
        // The mode name is distinct (so it coexists with DW Strong in the
        // lexer) but it must emit the same `strong_open`/`strong_close`
        // instructions so the XHTML renderer outputs <strong>.
        $this->P->addMode('gfm_strong_underscore', new GfmStrongUnderscore());
        $this->P->parse('__x__');
        $modes = array_column($this->H->calls, 0);
        $this->assertContains('strong_open', $modes);
        $this->assertNotContains('gfm_strong_underscore_open', $modes);
    }
}
