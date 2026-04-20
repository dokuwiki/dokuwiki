<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ModeRegistry;
use dokuwiki\Parsing\ParserMode\GfmEmphasisStrongUnderscore;

/**
 * Tests for the GFM em-wrapping-strong mode via underscores (`___text___`).
 *
 * Only the exact 3+3 symmetric variant is handled; intraword underscores
 * stay literal (multibyte-safe).
 */
class GfmEmphasisStrongUnderscoreTest extends ParserTestBase
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
        $this->P->addMode('gfm_emphasis_strong_underscore', new GfmEmphasisStrongUnderscore());
        $this->P->parse('Foo ___Bar___ Baz');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['emphasis_open', []],
            ['strong_open', []],
            ['cdata', ['Bar']],
            ['strong_close', []],
            ['emphasis_close', []],
            ['cdata', [' Baz']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testSingleCharacter()
    {
        $this->P->addMode('gfm_emphasis_strong_underscore', new GfmEmphasisStrongUnderscore());
        $this->P->parse('___a___');
        $this->assertContains('emphasis_open', array_column($this->H->calls, 0));
        $this->assertContains('strong_open', array_column($this->H->calls, 0));
    }

    function testLeadingWhitespaceDoesNotMatch()
    {
        $this->P->addMode('gfm_emphasis_strong_underscore', new GfmEmphasisStrongUnderscore());
        $this->P->parse('___ foo___');
        $this->assertNotContains('emphasis_open', array_column($this->H->calls, 0));
    }

    function testTrailingWhitespaceDoesNotMatch()
    {
        $this->P->addMode('gfm_emphasis_strong_underscore', new GfmEmphasisStrongUnderscore());
        $this->P->parse('___foo ___');
        $this->assertNotContains('emphasis_open', array_column($this->H->calls, 0));
    }

    function testDoesNotSpanParagraphBoundary()
    {
        $this->P->addMode('gfm_emphasis_strong_underscore', new GfmEmphasisStrongUnderscore());
        $this->P->parse("___foo\n\nbar___");
        $this->assertNotContains('emphasis_open', array_column($this->H->calls, 0));
    }

    function testLongerSymmetricRunDoesNotMatch()
    {
        $this->P->addMode('gfm_emphasis_strong_underscore', new GfmEmphasisStrongUnderscore());
        $this->P->parse('____foo____');
        $this->assertNotContains('emphasis_open', array_column($this->H->calls, 0));
    }

    function testAsymmetricDoesNotMatch()
    {
        $this->P->addMode('gfm_emphasis_strong_underscore', new GfmEmphasisStrongUnderscore());
        $this->P->parse('___foo__');
        $this->assertNotContains('emphasis_open', array_column($this->H->calls, 0));
    }

    function testIntrawordDoesNotMatch()
    {
        $this->P->addMode('gfm_emphasis_strong_underscore', new GfmEmphasisStrongUnderscore());
        $this->P->parse('abc___foo___');
        $this->assertNotContains('emphasis_open', array_column($this->H->calls, 0));
    }

    function testMultibyteIntrawordDoesNotMatch()
    {
        $this->P->addMode('gfm_emphasis_strong_underscore', new GfmEmphasisStrongUnderscore());
        $this->P->parse('für___etwas___');
        $this->assertNotContains('emphasis_open', array_column($this->H->calls, 0));
    }

    function testMultibyteContentInside()
    {
        $this->P->addMode('gfm_emphasis_strong_underscore', new GfmEmphasisStrongUnderscore());
        $this->P->parse('foo ___für___ bar');
        $this->assertContains('emphasis_open', array_column($this->H->calls, 0));
        $this->assertContains('strong_open', array_column($this->H->calls, 0));
    }

    function testSortValue()
    {
        $this->assertSame(65, (new GfmEmphasisStrongUnderscore())->getSort());
    }
}
