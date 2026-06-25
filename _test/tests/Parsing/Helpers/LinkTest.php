<?php

namespace dokuwiki\test\Parsing\Helpers;

use dokuwiki\Parsing\Helpers\Link;

/**
 * Tests for URL classification shared between Internallink and GfmLink.
 */
class LinkTest extends \DokuWikiTest
{
    function testClassifyInternalPageDefault()
    {
        $this->assertSame(
            ['internallink', ['some:page', 'Label']],
            Link::classify('some:page', 'Label')
        );
    }

    function testClassifyExternalHttp()
    {
        $this->assertSame(
            ['externallink', ['http://example.com', null]],
            Link::classify('http://example.com', null)
        );
    }

    function testClassifyExternalCustomScheme()
    {
        // Any `scheme://...` matches — the ladder does not validate against
        // the configured schemes list; that's the renderer's job.
        $this->assertSame(
            ['externallink', ['ftp://files.example.com/x', 'F']],
            Link::classify('ftp://files.example.com/x', 'F')
        );
    }

    function testClassifyInterwikiLink()
    {
        $this->assertSame(
            ['interwikilink', ['wp>Callback', 'cb', 'wp', 'Callback']],
            Link::classify('wp>Callback', 'cb')
        );
    }

    function testClassifyInterwikiPrefixLowercased()
    {
        [$call, $args] = Link::classify('IW>SomePage', 'T');
        $this->assertSame('interwikilink', $call);
        $this->assertSame('iw', $args[2], 'interwiki prefix must be lowercased');
        $this->assertSame('SomePage', $args[3], 'interwiki target must be preserved');
    }

    function testClassifyWindowsShare()
    {
        $this->assertSame(
            ['windowssharelink', ['\\\\server\\share', null]],
            Link::classify('\\\\server\\share', null)
        );
    }

    function testClassifyEmail()
    {
        $this->assertSame(
            ['emaillink', ['user@example.com', 'Mail']],
            Link::classify('user@example.com', 'Mail')
        );
    }

    function testClassifyLocalAnchorStripsHash()
    {
        $this->assertSame(
            ['locallink', ['section', 'Here']],
            Link::classify('#section', 'Here')
        );
    }

    function testClassifyInterwikiWinsOverEmail()
    {
        // An interwiki prefix containing `>` before an `@` still goes
        // interwiki. Order of the ladder is load-bearing.
        [$call, ] = Link::classify('wiki>user@host', 'x');
        $this->assertSame('interwikilink', $call);
    }

    function testClassifyArrayLabelForMediaInTitle()
    {
        // Internallink supports a parsed-media array as the label; the
        // helper must pass it through untouched.
        $media = ['type' => 'internalmedia', 'src' => 'img.gif'];
        [, $args] = Link::classify('some:page', $media);
        $this->assertSame($media, $args[1]);
    }
}
