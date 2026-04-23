<?php

namespace dokuwiki\test\Parsing\Helpers;

use dokuwiki\Parsing\Helpers\Media;

/**
 * Tests for media-URL parameter parsing shared between Media ({{...}})
 * and GfmMedia (![alt](url)).
 */
class MediaTest extends \DokuWikiTest
{
    function testParseMediaNoParameters()
    {
        $this->assertSame(
            ['src' => 'wiki:image.png', 'width' => null, 'height' => null,
             'cache' => 'cache', 'linking' => 'details', 'align' => null],
            Media::parseParameters('wiki:image.png')
        );
    }

    function testParseMediaWidthOnly()
    {
        $r = Media::parseParameters('wiki:image.png?200');
        $this->assertSame('wiki:image.png', $r['src']);
        $this->assertSame('200', $r['width']);
        $this->assertNull($r['height']);
    }

    function testParseMediaWidthAndHeight()
    {
        $r = Media::parseParameters('wiki:image.png?200x100');
        $this->assertSame('200', $r['width']);
        $this->assertSame('100', $r['height']);
    }

    function testParseMediaLinkingNolink()
    {
        $this->assertSame('nolink', Media::parseParameters('img.png?nolink')['linking']);
    }

    function testParseMediaLinkingDirect()
    {
        $this->assertSame('direct', Media::parseParameters('img.png?direct')['linking']);
    }

    function testParseMediaLinkingLinkonly()
    {
        $this->assertSame('linkonly', Media::parseParameters('img.png?linkonly')['linking']);
    }

    function testParseMediaCacheNocache()
    {
        $this->assertSame('nocache', Media::parseParameters('img.png?nocache')['cache']);
    }

    function testParseMediaCacheRecache()
    {
        $this->assertSame('recache', Media::parseParameters('img.png?recache')['cache']);
    }

    function testParseMediaCombinedParameters()
    {
        $r = Media::parseParameters('img.png?200x100&nolink&nocache');
        $this->assertSame('img.png', $r['src']);
        $this->assertSame('200', $r['width']);
        $this->assertSame('100', $r['height']);
        $this->assertSame('nolink', $r['linking']);
        $this->assertSame('nocache', $r['cache']);
    }

    function testParseMediaAlignLeft()
    {
        $this->assertSame('left', Media::parseParameters('img.png?left')['align']);
    }

    function testParseMediaAlignRight()
    {
        $this->assertSame('right', Media::parseParameters('img.png?right')['align']);
    }

    function testParseMediaAlignCenter()
    {
        $this->assertSame('center', Media::parseParameters('img.png?center')['align']);
    }

    function testParseMediaAlignAbsentIsNull()
    {
        $this->assertNull(Media::parseParameters('img.png?200x100')['align']);
        $this->assertNull(Media::parseParameters('img.png')['align']);
    }

    function testParseMediaAlignCaseInsensitive()
    {
        $this->assertSame('right', Media::parseParameters('img.png?RIGHT')['align']);
    }

    function testParseMediaAlignWithOtherParameters()
    {
        $r = Media::parseParameters('img.png?200x100&right&nocache');
        $this->assertSame('right', $r['align']);
        $this->assertSame('200', $r['width']);
        $this->assertSame('nocache', $r['cache']);
    }

    function testParseMediaLastQuestionMarkIsDelimiter()
    {
        // URLs may carry their own query string; split on the *last* `?`
        // so the URL query survives as part of the src.
        $r = Media::parseParameters('https://example.com/img?v=2?200x100');
        $this->assertSame('https://example.com/img?v=2', $r['src']);
        $this->assertSame('200', $r['width']);
        $this->assertSame('100', $r['height']);
    }
}
