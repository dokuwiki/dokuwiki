<?php

namespace dokuwiki\test\Parsing;

use dokuwiki\Parsing\Helpers;

/**
 * Tests for the pure-function helpers shared between DW and GFM modes:
 * URL classification (Internallink / GfmLink), media-URL parameter
 * parsing (Media / GfmMedia), and code-block attribute parsing (Code /
 * GfmCode / GfmFile).
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

    // ----- parseHighlightOptions ----------------------------------------

    public static function highlightOptionsProvider(): array
    {
        return [
            ['', null],
            ['something weird', null],
            ['enable_line_numbers', ['enable_line_numbers' => true]],
            ['enable_line_numbers=1', ['enable_line_numbers' => true]],
            ['enable_line_numbers="1"', ['enable_line_numbers' => true]],
            ['enable_line_numbers=0', ['enable_line_numbers' => false]],
            ['enable_line_numbers="0"', ['enable_line_numbers' => false]],
            ['enable_line_numbers=false', ['enable_line_numbers' => false]],
            ['enable_line_numbers="false"', ['enable_line_numbers' => false]],
            ['highlight_lines_extra', ['highlight_lines_extra' => [1]]],
            ['highlight_lines_extra=17', ['highlight_lines_extra' => [17]]],
            ['highlight_lines_extra=17,19', ['highlight_lines_extra' => [17, 19]]],
            ['highlight_lines_extra="17,19"', ['highlight_lines_extra' => [17, 19]]],
            ['highlight_lines_extra="17,19,17"', ['highlight_lines_extra' => [17, 19]]],
            ['start_line_numbers_at', ['start_line_numbers_at' => 1]],
            ['start_line_numbers_at=12', ['start_line_numbers_at' => 12]],
            ['start_line_numbers_at="12"', ['start_line_numbers_at' => 12]],
            ['enable_keyword_links', ['enable_keyword_links' => true]],
            ['enable_keyword_links=1', ['enable_keyword_links' => true]],
            ['enable_keyword_links="1"', ['enable_keyword_links' => true]],
            ['enable_keyword_links=0', ['enable_keyword_links' => false]],
            ['enable_keyword_links="0"', ['enable_keyword_links' => false]],
            ['enable_keyword_links=false', ['enable_keyword_links' => false]],
            ['enable_keyword_links="false"', ['enable_keyword_links' => false]],
            [
                'enable_line_numbers weird nothing highlight_lines_extra=17,19 start_line_numbers_at="12" enable_keyword_links=false',
                [
                    'enable_line_numbers' => true,
                    'highlight_lines_extra' => [17, 19],
                    'start_line_numbers_at' => 12,
                    'enable_keyword_links' => false
                ]
            ],
        ];
    }

    /**
     * @dataProvider highlightOptionsProvider
     */
    function testParseHighlightOptions(string $input, ?array $expect): void
    {
        $this->assertEquals($expect, Helpers::parseHighlightOptions($input));
    }

    // ----- parseCodeAttributes ------------------------------------------

    function testParseCodeAttributesEmpty()
    {
        $this->assertSame([null, null, null], Helpers::parseCodeAttributes(''));
    }

    function testParseCodeAttributesLanguageOnly()
    {
        $this->assertSame(['php', null, null], Helpers::parseCodeAttributes('php'));
    }

    function testParseCodeAttributesLanguageAndFilename()
    {
        $this->assertSame(
            ['php', 'myfile.php', null],
            Helpers::parseCodeAttributes('php myfile.php')
        );
    }

    function testParseCodeAttributesDashMeansNoLanguage()
    {
        // `-` is DokuWiki's explicit "no language" marker — lets a
        // filename follow without a language argument first.
        $this->assertSame(
            [null, 'myfile.txt', null],
            Helpers::parseCodeAttributes('- myfile.txt')
        );
    }

    function testParseCodeAttributesHtmlAliased()
    {
        // GeSHi's identifier for HTML is `html4strict`; DokuWiki
        // normalises `html` for author convenience.
        $this->assertSame(
            ['html4strict', null, null],
            Helpers::parseCodeAttributes('html')
        );
    }

    function testParseCodeAttributesOptionsOnly()
    {
        $this->assertSame(
            [null, null, ['enable_line_numbers' => true]],
            Helpers::parseCodeAttributes('[enable_line_numbers]')
        );
    }

    function testParseCodeAttributesLanguageAndOptions()
    {
        $this->assertSame(
            ['php', null, ['enable_line_numbers' => true]],
            Helpers::parseCodeAttributes('php [enable_line_numbers]')
        );
    }

    function testParseCodeAttributesLanguageFilenameAndOptions()
    {
        $this->assertSame(
            ['php', 'myfile.php', ['enable_line_numbers' => true]],
            Helpers::parseCodeAttributes('php myfile.php [enable_line_numbers]')
        );
    }

    function testParseCodeAttributesUnknownOptionsReturnsNull()
    {
        // Unknown keys in `[...]` are filtered out; the options slot
        // ends up null, same as if no `[...]` block had been present.
        $this->assertSame(
            ['C', null, null],
            Helpers::parseCodeAttributes('C [unknown="ignored"]')
        );
    }
}
