<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\GfmDeleted;

/**
 * Tests for the GFM strikethrough mode (`~~text~~`).
 */
class GfmDeletedTest extends ParserTestBase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->setSyntax('md');
    }

    public function testBasicStrikethrough()
    {
        $this->P->addMode('gfm_deleted', new GfmDeleted());
        $this->P->parse('Foo ~~Bar~~ Baz');

        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['deleted_open', []],
            ['cdata', ['Bar']],
            ['deleted_close', []],
            ['cdata', [' Baz']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    public function testSingleCharacterBody()
    {
        $this->P->addMode('gfm_deleted', new GfmDeleted());
        $this->P->parse('foo ~~b~~ bar');

        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nfoo "]],
            ['deleted_open', []],
            ['cdata', ['b']],
            ['deleted_close', []],
            ['cdata', [' bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    public function testMultipleWords()
    {
        $this->P->addMode('gfm_deleted', new GfmDeleted());
        $this->P->parse('~~three four five~~');

        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n"]],
            ['deleted_open', []],
            ['cdata', ['three four five']],
            ['deleted_close', []],
            ['cdata', ['']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    public function testTwoSeparateStrikethroughsOnOneLine()
    {
        $this->P->addMode('gfm_deleted', new GfmDeleted());
        $this->P->parse('~~one~~ and ~~two~~');

        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n"]],
            ['deleted_open', []],
            ['cdata', ['one']],
            ['deleted_close', []],
            ['cdata', [' and ']],
            ['deleted_open', []],
            ['cdata', ['two']],
            ['deleted_close', []],
            ['cdata', ['']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    public function testUnmatchedOpenerDoesNotStrike()
    {
        $this->P->addMode('gfm_deleted', new GfmDeleted());
        $this->P->parse('foo ~~bar with no closer');

        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nfoo ~~bar with no closer"]],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    public function testOpenerFollowedBySpaceDoesNotStrike()
    {
        $this->P->addMode('gfm_deleted', new GfmDeleted());
        $this->P->parse('foo ~~ bar~~ baz');

        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nfoo ~~ bar~~ baz"]],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    public function testEmptyDelimiterDoesNotStrike()
    {
        $this->P->addMode('gfm_deleted', new GfmDeleted());
        $this->P->parse('foo ~~~~ bar');

        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains('deleted_open', $modes,
            'Empty `~~~~` must stay literal');
    }

    public function testTripleTildeDoesNotStrike()
    {
        // `~~~` is the GFM fenced-code-block marker; strikethrough must not
        // consume a run of three or more tildes.
        $this->P->addMode('gfm_deleted', new GfmDeleted());
        $this->P->parse('foo ~~~bar~~~ baz');

        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains('deleted_open', $modes,
            'Run of 3+ tildes must not trigger strikethrough');
    }

    public function testMultilineStrikethrough()
    {
        $this->P->addMode('gfm_deleted', new GfmDeleted());
        $this->P->parse("~~line\nline\nline~~");

        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n"]],
            ['deleted_open', []],
            ['cdata', ["line\nline\nline"]],
            ['deleted_close', []],
            ['cdata', ['']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    public function testDoesNotSpanParagraphBoundary()
    {
        // An unclosed `~~` followed by a blank line must stay literal.
        // Mirrors GFM spec example 492.
        $this->P->addMode('gfm_deleted', new GfmDeleted());
        $this->P->parse("This ~~has a\n\nnew paragraph~~.");

        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains('deleted_open', $modes,
            'GfmDeleted must not open when the closing `~~` is past a blank line');
    }

    public function testAllowsSingleNewline()
    {
        $this->P->addMode('gfm_deleted', new GfmDeleted());
        $this->P->parse("~~open\nclose~~");

        $modes = array_column($this->H->calls, 0);
        $this->assertContains('deleted_open', $modes,
            'GfmDeleted must still match across a single newline');
    }

    public function testTrailingWhitespaceBeforeCloserDoesNotStrike()
    {
        $this->P->addMode('gfm_deleted', new GfmDeleted());
        $this->P->parse('~~foo bar ~~');

        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains('deleted_open', $modes,
            'Closer preceded by whitespace must not match');
    }

    public function testSortValue()
    {
        $mode = new GfmDeleted();
        $this->assertSame(130, $mode->getSort());
    }

    public function testRejectedOpenerBeforeValidSpanStaysLiteral()
    {
        $this->P->addMode('gfm_deleted', new GfmDeleted());
        $this->P->parse('~~ foo ~~bar~~');
        $this->assertContains('deleted_open', array_column($this->H->calls, 0));
        $this->assertStringContainsString('~~ foo ', $this->H->calls[2][1][0]);
    }

    public function testVeryLongSpanIsRecognized()
    {
        $body = trim(str_repeat('some words ', 7000)); // ~77KB
        $this->P->addMode('gfm_deleted', new GfmDeleted());
        $this->P->parse('~~' . $body . '~~');

        $calls = array_map(fn($call) => [$call[0], $call[1]], $this->H->calls);
        $this->assertContains(['deleted_open', []], $calls);
        $this->assertContains(['cdata', [$body]], $calls);
    }
}
