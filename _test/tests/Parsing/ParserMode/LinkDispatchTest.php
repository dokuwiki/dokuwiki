<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;
use dokuwiki\Parsing\ParserMode\LinkDispatch;

/**
 * Tests for the LinkDispatch trait: the shared URL-classification ladder
 * used by Internallink (`[[...]]`) and GfmLink (`[text](url)`).
 */
class LinkDispatchTest extends \DokuWikiTest
{
    /** An anonymous-class instance that exposes dispatchLink as public. */
    private function dispatcher(): object
    {
        return new class {
            use LinkDispatch {
                dispatchLink as public;
            }
        };
    }

    private function dispatch(string $url, $label = null): array
    {
        $handler = new Handler();
        $this->dispatcher()->dispatchLink($url, $label, 42, $handler);
        return $handler->calls;
    }

    function testInternalPageDefault()
    {
        $this->assertSame(
            [['internallink', ['some:page', 'Label'], 42]],
            $this->dispatch('some:page', 'Label')
        );
    }

    function testExternalHttp()
    {
        $this->assertSame(
            [['externallink', ['http://example.com', null], 42]],
            $this->dispatch('http://example.com')
        );
    }

    function testExternalCustomScheme()
    {
        // Any `scheme://...` matches — the ladder does not validate
        // against the configured schemes list; that's the renderer's job.
        $this->assertSame(
            [['externallink', ['ftp://files.example.com/x', 'F'], 42]],
            $this->dispatch('ftp://files.example.com/x', 'F')
        );
    }

    function testInterwikiLink()
    {
        $this->assertSame(
            [['interwikilink', ['wp>Callback', 'cb', 'wp', 'Callback'], 42]],
            $this->dispatch('wp>Callback', 'cb')
        );
    }

    function testInterwikiPrefixLowercased()
    {
        $calls = $this->dispatch('IW>SomePage', 'T');
        $this->assertSame('interwikilink', $calls[0][0]);
        $this->assertSame('iw', $calls[0][1][2], 'interwiki prefix must be lowercased');
        $this->assertSame('SomePage', $calls[0][1][3], 'interwiki target must be preserved');
    }

    function testWindowsShare()
    {
        $this->assertSame(
            [['windowssharelink', ['\\\\server\\share', null], 42]],
            $this->dispatch('\\\\server\\share')
        );
    }

    function testEmail()
    {
        $this->assertSame(
            [['emaillink', ['user@example.com', 'Mail'], 42]],
            $this->dispatch('user@example.com', 'Mail')
        );
    }

    function testLocalAnchorStripsHash()
    {
        $this->assertSame(
            [['locallink', ['section', 'Here'], 42]],
            $this->dispatch('#section', 'Here')
        );
    }

    function testInterwikiWinsOverEmail()
    {
        // An interwiki prefix containing `>` before an `@` still goes
        // interwiki. Order of the ladder is load-bearing.
        $calls = $this->dispatch('wiki>user@host', 'x');
        $this->assertSame('interwikilink', $calls[0][0]);
    }

    function testPositionIsPassedThrough()
    {
        $handler = new Handler();
        $this->dispatcher()->dispatchLink('page', null, 12345, $handler);
        $this->assertSame(12345, $handler->calls[0][2]);
    }

    function testArrayLabelForMediaInTitle()
    {
        // Internallink supports a parsed-media array as the label; the
        // trait must pass it through untouched.
        $media = ['type' => 'internalmedia', 'src' => 'img.gif'];
        $calls = $this->dispatch('some:page', $media);
        $this->assertSame($media, $calls[0][1][1]);
    }
}
