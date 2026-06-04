<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\GfmEmphasisStrong;

/**
 * Tests for the GFM em-wrapping-strong mode (`***text***`).
 *
 * Only the exact 3+3 symmetric variant is handled. Longer symmetric runs
 * (4+4, 6+6, ...) and asymmetric variants fall through to other modes or
 * stay literal.
 */
class GfmEmphasisStrongTest extends ParserTestBase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->setSyntax('md');
    }

    function testBasic()
    {
        $this->P->addMode('gfm_emphasis_strong', new GfmEmphasisStrong());
        $this->P->parse('Foo ***Bar*** Baz');
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
        $this->P->addMode('gfm_emphasis_strong', new GfmEmphasisStrong());
        $this->P->parse('***a***');
        $modes = array_column($this->H->calls, 0);
        $this->assertContains('emphasis_open', $modes);
        $this->assertContains('strong_open', $modes);
        $this->assertContains('strong_close', $modes);
        $this->assertContains('emphasis_close', $modes);
    }

    function testLeadingWhitespaceDoesNotMatch()
    {
        $this->P->addMode('gfm_emphasis_strong', new GfmEmphasisStrong());
        $this->P->parse('*** foo***');
        $this->assertNotContains('emphasis_open', array_column($this->H->calls, 0));
    }

    function testTrailingWhitespaceDoesNotMatch()
    {
        $this->P->addMode('gfm_emphasis_strong', new GfmEmphasisStrong());
        $this->P->parse('***foo ***');
        $this->assertNotContains('emphasis_open', array_column($this->H->calls, 0));
    }

    function testDoesNotSpanParagraphBoundary()
    {
        $this->P->addMode('gfm_emphasis_strong', new GfmEmphasisStrong());
        $this->P->parse("***foo\n\nbar***");
        $this->assertNotContains('emphasis_open', array_column($this->H->calls, 0));
    }

    function testLongerSymmetricRunDoesNotMatch()
    {
        // `****foo****` has 4 asterisks each side. The entry pattern requires
        // the opener run to be exactly 3 (via `(?<!\*)` and `(?!\*)` on the
        // closer), so this mode doesn't fire. It falls through to other
        // modes (which is tested via the full spec suite).
        $this->P->addMode('gfm_emphasis_strong', new GfmEmphasisStrong());
        $this->P->parse('****foo****');
        $this->assertNotContains('emphasis_open', array_column($this->H->calls, 0));
    }

    function testAsymmetricDoesNotMatch()
    {
        // `***foo**` has 3 asterisks on the left but only 2 on the right.
        // The entry's closing-delimiter lookahead requires exactly 3 `*`s
        // not followed by another `*`, so there's no valid closer.
        $this->P->addMode('gfm_emphasis_strong', new GfmEmphasisStrong());
        $this->P->parse('***foo**');
        $this->assertNotContains('emphasis_open', array_column($this->H->calls, 0));
    }

    function testSortValue()
    {
        $this->assertSame(65, (new GfmEmphasisStrong())->getSort());
    }
}
