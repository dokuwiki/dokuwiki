<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\Eol;
use dokuwiki\Parsing\ParserMode\GfmFile;

/**
 * Tests for GFM tilde-fenced code blocks (`GfmFile`).
 */
class GfmFileTest extends ParserTestBase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->setSyntax('md');
    }

    private function addModes(): void
    {
        $this->P->addMode('gfm_file', new GfmFile());
        $this->P->addMode('eol', new Eol());
    }

    function testBasicTildeFence()
    {
        $this->addModes();
        $this->P->parse("~~~\nhello\n~~~");
        $fileCalls = array_values(array_filter(
            $this->H->calls,
            static fn($c) => $c[0] === 'file'
        ));
        $this->assertCount(1, $fileCalls);
        $this->assertSame("hello\n", $fileCalls[0][1][0]);
        $this->assertNull($fileCalls[0][1][1]);
        $this->assertNull($fileCalls[0][1][2]);
    }

    function testLanguageFromInfoString()
    {
        $this->addModes();
        $this->P->parse("~~~ruby\nx\n~~~");
        $fileCalls = array_values(array_filter(
            $this->H->calls,
            static fn($c) => $c[0] === 'file'
        ));
        $this->assertCount(1, $fileCalls);
        $this->assertSame('ruby', $fileCalls[0][1][1]);
    }

    function testTildeInfoAcceptsBackticks()
    {
        // GFM spec example 116: tilde fences allow backticks in the info
        // string; first word is the language.
        $this->addModes();
        $this->P->parse("~~~ aa ``` ~~~\nfoo\n~~~");
        $fileCalls = array_values(array_filter(
            $this->H->calls,
            static fn($c) => $c[0] === 'file'
        ));
        $this->assertCount(1, $fileCalls);
        $this->assertSame('aa', $fileCalls[0][1][1]);
    }

    function testBacktickLineDoesNotCloseTildeFence()
    {
        $this->addModes();
        $this->P->parse("~~~\naaa\n```\nbbb\n~~~");
        $fileCalls = array_values(array_filter(
            $this->H->calls,
            static fn($c) => $c[0] === 'file'
        ));
        $this->assertCount(1, $fileCalls);
        $this->assertSame("aaa\n```\nbbb\n", $fileCalls[0][1][0]);
    }

    function testUnclosedFenceStaysLiteral()
    {
        // Unclosed fences stay literal — same rule as GfmCode. See
        // GfmCode class docblock for the rationale.
        $this->addModes();
        $this->P->parse("~~~\nabc\ndef");
        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains('file', $modes,
            'Unclosed tilde fences must stay literal, not emit file');
    }

    function testEmptyBody()
    {
        $this->addModes();
        $this->P->parse("~~~\n~~~");
        $fileCalls = array_values(array_filter(
            $this->H->calls,
            static fn($c) => $c[0] === 'file'
        ));
        $this->assertCount(1, $fileCalls);
        $this->assertSame('', $fileCalls[0][1][0]);
    }

    function testSortValue()
    {
        $this->assertSame(210, (new GfmFile())->getSort());
    }
}
