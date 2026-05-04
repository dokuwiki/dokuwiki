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
        $conf['syntax'] = 'md';
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

    // ----- image-as-label (`[![alt](img)](target)`) -----

    /**
     * Media descriptor shape GfmLink emits for image-as-label, matching
     * what Media::parseMedia() returns.
     */
    private function mediaArray(array $overrides): array
    {
        return array_merge([
            'type'    => 'internalmedia',
            'src'     => 'wiki:image.png',
            'title'   => 'alt',
            'align'   => null,
            'width'   => null,
            'height'  => null,
            'cache'   => 'cache',
            'linking' => 'details',
        ], $overrides);
    }

    function testImageAsLabelInternalPageLink()
    {
        // The canonical case: image that links to a wiki page.
        // Markdown equivalent of DW's `[[test:link|{{wiki:image.png}}]]`.
        $this->P->addMode('gfm_link', new GfmLink());
        $this->P->parse('Foo [![alt](wiki:image.png)](test:link) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['internallink', ['test:link', $this->mediaArray([])]],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testImageAsLabelExternalLink()
    {
        $this->P->addMode('gfm_link', new GfmLink());
        $this->P->parse('Foo [![alt](wiki:image.png)](http://example.com) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['externallink', ['http://example.com', $this->mediaArray([])]],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testImageAsLabelWithExternalMedia()
    {
        $this->P->addMode('gfm_link', new GfmLink());
        $this->P->parse('Foo [![logo](https://example.com/logo.png)](test:link) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['internallink', ['test:link', $this->mediaArray([
                'type'  => 'externalmedia',
                'src'   => 'https://example.com/logo.png',
                'title' => 'logo',
            ])]],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testImageAsLabelInterwikiLink()
    {
        $this->P->addMode('gfm_link', new GfmLink());
        $this->P->parse('Foo [![alt](wiki:image.png)](wp>Example) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['interwikilink', ['wp>Example', $this->mediaArray([]), 'wp', 'Example']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testImageAsLabelEmailLink()
    {
        $this->P->addMode('gfm_link', new GfmLink());
        $this->P->parse('Foo [![alt](wiki:image.png)](user@example.com) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['emaillink', ['user@example.com', $this->mediaArray([])]],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testImageAsLabelMediaParameters()
    {
        // Full DW parameter vocabulary works in the nested image slot.
        $this->P->addMode('gfm_link', new GfmLink());
        $this->P->parse('Foo [![alt](wiki:image.png?200x100&right&nolink)](test:link) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['internallink', ['test:link', $this->mediaArray([
                'align'   => 'right',
                'width'   => '200',
                'height'  => '100',
                'linking' => 'nolink',
            ])]],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testImageAsLabelEmptyAlt()
    {
        $this->P->addMode('gfm_link', new GfmLink());
        $this->P->parse('Foo [![](wiki:image.png)](test:link) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['internallink', ['test:link', $this->mediaArray(['title' => null])]],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testImageAsLabelBothTitlesDiscarded()
    {
        // Titles on both URLs parse cleanly but are dropped — neither
        // DW's media nor link instructions have a title-attribute slot.
        $this->P->addMode('gfm_link', new GfmLink());
        $this->P->parse('Foo [![alt](wiki:image.png "img title")](test:link "link title") Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['internallink', ['test:link', $this->mediaArray([])]],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    // ----- backslash-escape interaction (GFM §6.1) -----

    function testBackslashEscapesInLabel()
    {
        // Plain-text label gets §6.1 unescape applied before it reaches
        // the link handler — `\*` collapses to a literal `*`.
        $this->P->addMode('gfm_link', new GfmLink());
        $this->P->parse('Foo [te\\*xt](page) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['internallink', ['page', 'te*xt']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testBackslashEscapesInUrl()
    {
        // §6.1 unescape fires on the URL after classify() picks the
        // handler — it lets users put a literal punctuation char in a
        // URL slot that would otherwise carry markup meaning.
        $this->P->addMode('gfm_link', new GfmLink());
        $this->P->parse('Foo [text](http://example.com/pa\\!ge) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['externallink', ['http://example.com/pa!ge', 'text']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testWindowsShareUrlSkipsBackslashUnescape()
    {
        // Carve-out: a `\\host\path` URL must survive classify() and
        // stay intact as a windowssharelink. Applying §6.1 unescape
        // would collapse the leading `\\` to `\` and destroy the share
        // marker, so the unescape pass is skipped for this classifier.
        $this->P->addMode('gfm_link', new GfmLink());
        $this->P->parse('Foo [share](\\\\server\\share\\sub) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['windowssharelink', ['\\\\server\\share\\sub', 'share']],
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
