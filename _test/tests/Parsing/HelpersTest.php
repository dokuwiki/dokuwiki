<?php

namespace dokuwiki\test\Parsing;

use dokuwiki\Parsing\Helpers;

/**
 * Tests for the pure-function helpers shared between DW and GFM modes:
 * URL classification (Internallink / GfmLink) and media-URL parameter
 * parsing (Media / GfmMedia).
 */
class HelpersTest extends \DokuWikiTest
{
    // ----- classifyLink --------------------------------------------------

    function testClassifyInternalPageDefault()
    {
        $this->assertSame(
            ['internallink', ['some:page', 'Label']],
            Helpers::classifyLink('some:page', 'Label')
        );
    }

    function testClassifyExternalHttp()
    {
        $this->assertSame(
            ['externallink', ['http://example.com', null]],
            Helpers::classifyLink('http://example.com', null)
        );
    }

    function testClassifyExternalCustomScheme()
    {
        // Any `scheme://...` matches — the ladder does not validate against
        // the configured schemes list; that's the renderer's job.
        $this->assertSame(
            ['externallink', ['ftp://files.example.com/x', 'F']],
            Helpers::classifyLink('ftp://files.example.com/x', 'F')
        );
    }

    function testClassifyInterwikiLink()
    {
        $this->assertSame(
            ['interwikilink', ['wp>Callback', 'cb', 'wp', 'Callback']],
            Helpers::classifyLink('wp>Callback', 'cb')
        );
    }

    function testClassifyInterwikiPrefixLowercased()
    {
        [$call, $args] = Helpers::classifyLink('IW>SomePage', 'T');
        $this->assertSame('interwikilink', $call);
        $this->assertSame('iw', $args[2], 'interwiki prefix must be lowercased');
        $this->assertSame('SomePage', $args[3], 'interwiki target must be preserved');
    }

    function testClassifyWindowsShare()
    {
        $this->assertSame(
            ['windowssharelink', ['\\\\server\\share', null]],
            Helpers::classifyLink('\\\\server\\share', null)
        );
    }

    function testClassifyEmail()
    {
        $this->assertSame(
            ['emaillink', ['user@example.com', 'Mail']],
            Helpers::classifyLink('user@example.com', 'Mail')
        );
    }

    function testClassifyLocalAnchorStripsHash()
    {
        $this->assertSame(
            ['locallink', ['section', 'Here']],
            Helpers::classifyLink('#section', 'Here')
        );
    }

    function testClassifyInterwikiWinsOverEmail()
    {
        // An interwiki prefix containing `>` before an `@` still goes
        // interwiki. Order of the ladder is load-bearing.
        [$call, ] = Helpers::classifyLink('wiki>user@host', 'x');
        $this->assertSame('interwikilink', $call);
    }

    function testClassifyArrayLabelForMediaInTitle()
    {
        // Internallink supports a parsed-media array as the label; the
        // helper must pass it through untouched.
        $media = ['type' => 'internalmedia', 'src' => 'img.gif'];
        [, $args] = Helpers::classifyLink('some:page', $media);
        $this->assertSame($media, $args[1]);
    }

    // ----- parseMediaParameters -----------------------------------------

    function testParseMediaNoParameters()
    {
        $this->assertSame(
            ['src' => 'wiki:image.png', 'width' => null, 'height' => null,
             'cache' => 'cache', 'linking' => 'details', 'align' => null],
            Helpers::parseMediaParameters('wiki:image.png')
        );
    }

    function testParseMediaWidthOnly()
    {
        $r = Helpers::parseMediaParameters('wiki:image.png?200');
        $this->assertSame('wiki:image.png', $r['src']);
        $this->assertSame('200', $r['width']);
        $this->assertNull($r['height']);
    }

    function testParseMediaWidthAndHeight()
    {
        $r = Helpers::parseMediaParameters('wiki:image.png?200x100');
        $this->assertSame('200', $r['width']);
        $this->assertSame('100', $r['height']);
    }

    function testParseMediaLinkingNolink()
    {
        $this->assertSame('nolink', Helpers::parseMediaParameters('img.png?nolink')['linking']);
    }

    function testParseMediaLinkingDirect()
    {
        $this->assertSame('direct', Helpers::parseMediaParameters('img.png?direct')['linking']);
    }

    function testParseMediaLinkingLinkonly()
    {
        $this->assertSame('linkonly', Helpers::parseMediaParameters('img.png?linkonly')['linking']);
    }

    function testParseMediaCacheNocache()
    {
        $this->assertSame('nocache', Helpers::parseMediaParameters('img.png?nocache')['cache']);
    }

    function testParseMediaCacheRecache()
    {
        $this->assertSame('recache', Helpers::parseMediaParameters('img.png?recache')['cache']);
    }

    function testParseMediaCombinedParameters()
    {
        $r = Helpers::parseMediaParameters('img.png?200x100&nolink&nocache');
        $this->assertSame('img.png', $r['src']);
        $this->assertSame('200', $r['width']);
        $this->assertSame('100', $r['height']);
        $this->assertSame('nolink', $r['linking']);
        $this->assertSame('nocache', $r['cache']);
    }

    function testParseMediaAlignLeft()
    {
        $this->assertSame('left', Helpers::parseMediaParameters('img.png?left')['align']);
    }

    function testParseMediaAlignRight()
    {
        $this->assertSame('right', Helpers::parseMediaParameters('img.png?right')['align']);
    }

    function testParseMediaAlignCenter()
    {
        $this->assertSame('center', Helpers::parseMediaParameters('img.png?center')['align']);
    }

    function testParseMediaAlignAbsentIsNull()
    {
        $this->assertNull(Helpers::parseMediaParameters('img.png?200x100')['align']);
        $this->assertNull(Helpers::parseMediaParameters('img.png')['align']);
    }

    function testParseMediaAlignCaseInsensitive()
    {
        $this->assertSame('right', Helpers::parseMediaParameters('img.png?RIGHT')['align']);
    }

    function testParseMediaAlignWithOtherParameters()
    {
        $r = Helpers::parseMediaParameters('img.png?200x100&right&nocache');
        $this->assertSame('right', $r['align']);
        $this->assertSame('200', $r['width']);
        $this->assertSame('nocache', $r['cache']);
    }

    function testParseMediaLastQuestionMarkIsDelimiter()
    {
        // URLs may carry their own query string; split on the *last* `?`
        // so the URL query survives as part of the src.
        $r = Helpers::parseMediaParameters('https://example.com/img?v=2?200x100');
        $this->assertSame('https://example.com/img?v=2', $r['src']);
        $this->assertSame('200', $r['width']);
        $this->assertSame('100', $r['height']);
    }
}
