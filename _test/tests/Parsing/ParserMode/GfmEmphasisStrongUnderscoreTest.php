<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\GfmEmphasisStrongUnderscore;

/**
 * Tests for the GFM em-wrapping-strong mode via underscores (`___text___`).
 *
 * Only the exact 3+3 symmetric variant is handled; intraword underscores
 * stay literal
 */
class GfmEmphasisStrongUnderscoreTest extends ParserTestBase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->setSyntax('md');
    }

    public function testBasic()
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

    public function testSingleCharacter()
    {
        $this->P->addMode('gfm_emphasis_strong_underscore', new GfmEmphasisStrongUnderscore());
        $this->P->parse('___a___');
        $this->assertContains('emphasis_open', array_column($this->H->calls, 0));
        $this->assertContains('strong_open', array_column($this->H->calls, 0));
    }

    public function testLeadingWhitespaceDoesNotMatch()
    {
        $this->P->addMode('gfm_emphasis_strong_underscore', new GfmEmphasisStrongUnderscore());
        $this->P->parse('___ foo___');
        $this->assertNotContains('emphasis_open', array_column($this->H->calls, 0));
    }

    public function testTrailingWhitespaceDoesNotMatch()
    {
        $this->P->addMode('gfm_emphasis_strong_underscore', new GfmEmphasisStrongUnderscore());
        $this->P->parse('___foo ___');
        $this->assertNotContains('emphasis_open', array_column($this->H->calls, 0));
    }

    public function testDoesNotSpanParagraphBoundary()
    {
        $this->P->addMode('gfm_emphasis_strong_underscore', new GfmEmphasisStrongUnderscore());
        $this->P->parse("___foo\n\nbar___");
        $this->assertNotContains('emphasis_open', array_column($this->H->calls, 0));
    }

    public function testLongerSymmetricRunDoesNotMatch()
    {
        $this->P->addMode('gfm_emphasis_strong_underscore', new GfmEmphasisStrongUnderscore());
        $this->P->parse('____foo____');
        $this->assertNotContains('emphasis_open', array_column($this->H->calls, 0));
    }

    public function testAsymmetricDoesNotMatch()
    {
        $this->P->addMode('gfm_emphasis_strong_underscore', new GfmEmphasisStrongUnderscore());
        $this->P->parse('___foo__');
        $this->assertNotContains('emphasis_open', array_column($this->H->calls, 0));
    }

    public function testIntrawordDoesNotMatch()
    {
        $this->P->addMode('gfm_emphasis_strong_underscore', new GfmEmphasisStrongUnderscore());
        $this->P->parse('abc___foo___');
        $this->assertNotContains('emphasis_open', array_column($this->H->calls, 0));
    }

    public function testMultibyteIntrawordDoesNotMatch()
    {
        $this->P->addMode('gfm_emphasis_strong_underscore', new GfmEmphasisStrongUnderscore());
        $this->P->parse('für___etwas___');
        $this->assertNotContains('emphasis_open', array_column($this->H->calls, 0));
    }

    public function testMultibyteContentInside()
    {
        $this->P->addMode('gfm_emphasis_strong_underscore', new GfmEmphasisStrongUnderscore());
        $this->P->parse('foo ___für___ bar');
        $this->assertContains('emphasis_open', array_column($this->H->calls, 0));
        $this->assertContains('strong_open', array_column($this->H->calls, 0));
    }

    public function testSortValue()
    {
        $this->assertSame(65, (new GfmEmphasisStrongUnderscore())->getSort());
    }

    public function testRejectedOpenerBeforeValidSpanStaysLiteral()
    {
        $this->P->addMode('gfm_emphasis_strong_underscore', new GfmEmphasisStrongUnderscore());
        $this->P->parse('___ foo ___bar___');
        $this->assertContains('emphasis_open', array_column($this->H->calls, 0));
        $this->assertStringContainsString('___ foo ', $this->H->calls[2][1][0]);
    }
}
