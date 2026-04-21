<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ModeRegistry;
use dokuwiki\Parsing\ParserMode\GfmDeleted;

/**
 * Tests for the GFM strikethrough mode (`~~text~~`).
 */
class GfmDeletedTest extends ParserTestBase
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

    function testBasicStrikethrough()
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

    function testSingleCharacterBody()
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

    function testMultipleWords()
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

    function testTwoSeparateStrikethroughsOnOneLine()
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

    function testUnmatchedOpenerDoesNotStrike()
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

    function testOpenerFollowedBySpaceDoesNotStrike()
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

    function testEmptyDelimiterDoesNotStrike()
    {
        $this->P->addMode('gfm_deleted', new GfmDeleted());
        $this->P->parse('foo ~~~~ bar');
        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains('deleted_open', $modes,
            'Empty `~~~~` must stay literal');
    }

    function testTripleTildeDoesNotStrike()
    {
        // `~~~` is the GFM fenced-code-block marker; strikethrough must not
        // consume a run of three or more tildes.
        $this->P->addMode('gfm_deleted', new GfmDeleted());
        $this->P->parse('foo ~~~bar~~~ baz');
        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains('deleted_open', $modes,
            'Run of 3+ tildes must not trigger strikethrough');
    }

    function testMultilineStrikethrough()
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

    function testDoesNotSpanParagraphBoundary()
    {
        // An unclosed `~~` followed by a blank line must stay literal.
        // Mirrors GFM spec example 492.
        $this->P->addMode('gfm_deleted', new GfmDeleted());
        $this->P->parse("This ~~has a\n\nnew paragraph~~.");
        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains('deleted_open', $modes,
            'GfmDeleted must not open when the closing `~~` is past a blank line');
    }

    function testAllowsSingleNewline()
    {
        $this->P->addMode('gfm_deleted', new GfmDeleted());
        $this->P->parse("~~open\nclose~~");
        $modes = array_column($this->H->calls, 0);
        $this->assertContains('deleted_open', $modes,
            'GfmDeleted must still match across a single newline');
    }

    function testTrailingWhitespaceBeforeCloserDoesNotStrike()
    {
        $this->P->addMode('gfm_deleted', new GfmDeleted());
        $this->P->parse('~~foo bar ~~');
        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains('deleted_open', $modes,
            'Closer preceded by whitespace must not match');
    }

    function testSortValue()
    {
        $mode = new GfmDeleted();
        $this->assertSame(130, $mode->getSort());
    }
}
