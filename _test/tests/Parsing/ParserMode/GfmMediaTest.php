<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ParserMode\GfmLink;
use dokuwiki\Parsing\ParserMode\GfmMedia;

/**
 * Tests for GFM inline image syntax `![alt](url)` dispatching to DokuWiki's
 * internalmedia / externalmedia handler instructions.
 */
class GfmMediaTest extends ParserTestBase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->setSyntax('md');
    }

    function testInternalMedia()
    {
        $this->P->addMode('gfm_media', new GfmMedia());
        $this->P->parse('Foo ![alt](wiki:image.png) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['internalmedia', ['wiki:image.png', 'alt', null, null, null, 'cache', 'details']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testExternalMediaHttps()
    {
        $this->P->addMode('gfm_media', new GfmMedia());
        $this->P->parse('Foo ![logo](https://example.com/img.png) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['externalmedia', ['https://example.com/img.png', 'logo', null, null, null, 'cache', 'details']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testExternalMediaInterwiki()
    {
        $this->P->addMode('gfm_media', new GfmMedia());
        $this->P->parse('Foo ![foo](wp>Example.png) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['externalmedia', ['wp>Example.png', 'foo', null, null, null, 'cache', 'details']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testEmptyAlt()
    {
        // GFM allows `![](/url)` with empty alt; we pass null in the caption
        // slot to match how DW's Media mode emits no-caption media.
        $this->P->addMode('gfm_media', new GfmMedia());
        $this->P->parse('Foo ![](image.png) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['internalmedia', ['image.png', null, null, null, null, 'cache', 'details']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testTitleInDoubleQuotesIsDiscarded()
    {
        // GFM allows ![alt](url "title") but DokuWiki's media handler has
        // no separate title slot (alt doubles as caption). The title parses
        // cleanly but is dropped.
        $this->P->addMode('gfm_media', new GfmMedia());
        $this->P->parse('Foo ![alt](wiki:image.png "caption") Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['internalmedia', ['wiki:image.png', 'alt', null, null, null, 'cache', 'details']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testTitleInSingleQuotesIsDiscarded()
    {
        $this->P->addMode('gfm_media', new GfmMedia());
        $this->P->parse("Foo ![alt](wiki:image.png 'caption') Bar");
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['internalmedia', ['wiki:image.png', 'alt', null, null, null, 'cache', 'details']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testMultibyteAlt()
    {
        $this->P->addMode('gfm_media', new GfmMedia());
        $this->P->parse('Foo ![日本語](pic.png) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['internalmedia', ['pic.png', '日本語', null, null, null, 'cache', 'details']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testPlainLinkNotImage()
    {
        // With both gfm_media and gfm_link loaded, `[text](url)` (no leading
        // `!`) must dispatch through gfm_link, not gfm_media.
        $this->P->addMode('gfm_media', new GfmMedia());
        $this->P->addMode('gfm_link', new GfmLink());
        $this->P->parse('Foo [text](page) Bar');
        $modes = array_column($this->H->calls, 0);
        $this->assertContains('internallink', $modes);
        $this->assertNotContains('internalmedia', $modes);
    }

    function testBangAloneIsNotImage()
    {
        $this->P->addMode('gfm_media', new GfmMedia());
        $this->P->parse('Foo ![alt] Bar');
        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains('internalmedia', $modes);
        $this->assertNotContains('externalmedia', $modes);
    }

    function testSpaceBetweenBracketsAndParensIsNotAnImage()
    {
        $this->P->addMode('gfm_media', new GfmMedia());
        $this->P->parse('![foo] (bar)');
        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains('internalmedia', $modes);
        $this->assertNotContains('externalmedia', $modes);
    }

    function testReferenceStyleImageNotMatched()
    {
        $this->P->addMode('gfm_media', new GfmMedia());
        $this->P->parse('![foo][bar]');
        $modes = array_column($this->H->calls, 0);
        $this->assertNotContains('internalmedia', $modes);
        $this->assertNotContains('externalmedia', $modes);
    }

    function testTwoImagesInOneLine()
    {
        $this->P->addMode('gfm_media', new GfmMedia());
        $this->P->parse('Foo ![one](a.png) and ![two](b.png) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['internalmedia', ['a.png', 'one', null, null, null, 'cache', 'details']],
            ['cdata', [' and ']],
            ['internalmedia', ['b.png', 'two', null, null, null, 'cache', 'details']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testWidthOnlyParameter()
    {
        $this->P->addMode('gfm_media', new GfmMedia());
        $this->P->parse('Foo ![alt](wiki:image.png?200) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['internalmedia', ['wiki:image.png', 'alt', null, '200', null, 'cache', 'details']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testWidthAndHeightParameter()
    {
        $this->P->addMode('gfm_media', new GfmMedia());
        $this->P->parse('Foo ![alt](wiki:image.png?200x100) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['internalmedia', ['wiki:image.png', 'alt', null, '200', '100', 'cache', 'details']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testLinkingParameter()
    {
        $this->P->addMode('gfm_media', new GfmMedia());
        $this->P->parse('Foo ![alt](wiki:image.png?nolink) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['internalmedia', ['wiki:image.png', 'alt', null, null, null, 'cache', 'nolink']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testCacheParameter()
    {
        $this->P->addMode('gfm_media', new GfmMedia());
        $this->P->parse('Foo ![alt](wiki:image.png?recache) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['internalmedia', ['wiki:image.png', 'alt', null, null, null, 'recache', 'details']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testCombinedParameters()
    {
        $this->P->addMode('gfm_media', new GfmMedia());
        $this->P->parse('Foo ![alt](wiki:image.png?200x100&nolink&nocache) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['internalmedia', ['wiki:image.png', 'alt', null, '200', '100', 'nocache', 'nolink']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testLastQuestionMarkIsTheParameterDelimiter()
    {
        // URLs may carry their own query string; DW splits on the *last* `?`
        // so the URL query survives as part of the src.
        $this->P->addMode('gfm_media', new GfmMedia());
        $this->P->parse('Foo ![alt](https://example.com/img?v=2?200x100) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['externalmedia', ['https://example.com/img?v=2', 'alt', null, '200', '100', 'cache', 'details']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testAlignRight()
    {
        // GFM has no native image-alignment syntax, so GfmMedia borrows
        // DW's ?right/?left/?center keyword from the shared URL parameter
        // block — the only way to align an inline GFM image.
        $this->P->addMode('gfm_media', new GfmMedia());
        $this->P->parse('Foo ![alt](wiki:image.png?right) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['internalmedia', ['wiki:image.png', 'alt', 'right', null, null, 'cache', 'details']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testAlignWithSizeAndLinking()
    {
        $this->P->addMode('gfm_media', new GfmMedia());
        $this->P->parse('Foo ![alt](wiki:image.png?200x100&center&nolink) Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\nFoo "]],
            ['internalmedia', ['wiki:image.png', 'alt', 'center', '200', '100', 'cache', 'nolink']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testSortValue()
    {
        $this->assertSame(310, (new GfmMedia())->getSort());
    }
}
