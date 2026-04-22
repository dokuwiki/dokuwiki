<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ModeRegistry;
use dokuwiki\Parsing\ParserMode\GfmLink;
use dokuwiki\Parsing\ParserMode\Internallink;

/**
 * Tests for GFM inline links `[text](url)` dispatching to DokuWiki's
 * internal / external / interwiki / email / windowsshare / local link
 * handler instructions.
 */
class GfmLinkTest extends ParserTestBase
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

    function testInternalPage()
    {
        $this->P->addMode('gfm_link', new GfmLink());
        $this->P->parse('Foo [text](page) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['internallink', ['page', 'text']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testInternalPageWithNamespace()
    {
        $this->P->addMode('gfm_link', new GfmLink());
        $this->P->parse('Foo [Syntax](wiki:syntax#internal) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['internallink', ['wiki:syntax#internal', 'Syntax']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testExternalLink()
    {
        $this->P->addMode('gfm_link', new GfmLink());
        $this->P->parse('Foo [Google](http://google.com) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['externallink', ['http://google.com', 'Google']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testInterwikiLink()
    {
        $this->P->addMode('gfm_link', new GfmLink());
        $this->P->parse('Foo [callbacks](wp>Callback) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['interwikilink', ['wp>Callback', 'callbacks', 'wp', 'Callback']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testInterwikiLinkCaseNormalized()
    {
        $this->P->addMode('gfm_link', new GfmLink());
        $this->P->parse('Foo [Page](IW>somepage) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['interwikilink', ['IW>somepage', 'Page', 'iw', 'somepage']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testEmailLink()
    {
        $this->P->addMode('gfm_link', new GfmLink());
        $this->P->parse('Foo [mail](user@example.com) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['emaillink', ['user@example.com', 'mail']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testLocalAnchor()
    {
        $this->P->addMode('gfm_link', new GfmLink());
        $this->P->parse('Foo [section](#anchor) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['locallink', ['anchor', 'section']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testWindowsShare()
    {
        $this->P->addMode('gfm_link', new GfmLink());
        $this->P->parse('Foo [share](\\\\server\\share) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['windowssharelink', ['\\\\server\\share', 'share']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testTitleInDoubleQuotesIsDiscarded()
    {
        // GFM allows [text](url "title") but DokuWiki's link handler
        // instructions have no title-attribute slot. The title parses
        // cleanly but is dropped; the resulting handler call is identical
        // to the no-title case.
        $this->P->addMode('gfm_link', new GfmLink());
        $this->P->parse('Foo [Google](http://google.com "Search engine") Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['externallink', ['http://google.com', 'Google']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testTitleInSingleQuotesIsDiscarded()
    {
        $this->P->addMode('gfm_link', new GfmLink());
        $this->P->parse("Foo [page](target 'a title') Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['internallink', ['target', 'page']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testSpaceBetweenBracketsAndParensIsNotALink()
    {
        // GFM explicitly forbids whitespace between `]` and `(`.
        $this->P->addMode('gfm_link', new GfmLink());
        $this->P->parse('[foo] (bar)');
        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains('internallink', $modes);
        $this->assertNotContains('externallink', $modes);
    }

    function testDwDoubleBracketNotConsumedByGfmLink()
    {
        // With both gfm_link and DW internallink loaded (mixed syntax),
        // `[[foo]]` must go to Internallink. GfmLink's `\[(?!\[)` guard
        // refuses single-bracket matches that are actually part of `[[`.
        $this->P->addMode('gfm_link', new GfmLink());
        $this->P->addMode('internallink', new Internallink());
        $this->P->parse('Foo [[bar]] Baz');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['internallink', ['bar', null]],
            ['cdata', [' Baz']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMultibyteLinkText()
    {
        $this->P->addMode('gfm_link', new GfmLink());
        $this->P->parse('Foo [日本語](page) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['internallink', ['page', '日本語']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testReferenceStyleLinkNotMatched()
    {
        // `[foo][bar]` (reference-style) requires a reference definition
        // we do not support; each `[...]` should stay literal text.
        $this->P->addMode('gfm_link', new GfmLink());
        $this->P->parse('[foo][bar]');
        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains('internallink', $modes);
        $this->assertNotContains('externallink', $modes);
    }

    function testTwoLinksInOneLine()
    {
        $this->P->addMode('gfm_link', new GfmLink());
        $this->P->parse('Foo [one](a) and [two](b) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['internallink', ['a', 'one']],
            ['cdata', [' and ']],
            ['internallink', ['b', 'two']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testFragmentInExternalUrl()
    {
        $this->P->addMode('gfm_link', new GfmLink());
        $this->P->parse('Foo [x](http://example.com#fragment) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['externallink', ['http://example.com#fragment', 'x']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testSortValue()
    {
        $this->assertSame(300, (new GfmLink())->getSort());
    }
}
