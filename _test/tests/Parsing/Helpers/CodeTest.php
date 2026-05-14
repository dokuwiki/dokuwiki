<?php

namespace dokuwiki\test\Parsing\Helpers;

use dokuwiki\Parsing\Helpers\Code;

/**
 * Tests for code-block attribute parsing shared between Code / File
 * (DokuWiki) and GfmCode / GfmFile.
 */
class CodeTest extends \DokuWikiTest
{
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
        $this->assertEquals($expect, Code::parseHighlightOptions($input));
    }

    // ----- parseAttributes ------------------------------------------

    function testParseCodeAttributesEmpty()
    {
        $this->assertSame([null, null, null], Code::parseAttributes(''));
    }

    function testParseCodeAttributesLanguageOnly()
    {
        $this->assertSame(['php', null, null], Code::parseAttributes('php'));
    }

    function testParseCodeAttributesLanguageAndFilename()
    {
        $this->assertSame(
            ['php', 'myfile.php', null],
            Code::parseAttributes('php myfile.php')
        );
    }

    function testParseCodeAttributesDashMeansNoLanguage()
    {
        // `-` is DokuWiki's explicit "no language" marker — lets a
        // filename follow without a language argument first.
        $this->assertSame(
            [null, 'myfile.txt', null],
            Code::parseAttributes('- myfile.txt')
        );
    }

    function testParseCodeAttributesHtmlAliased()
    {
        // GeSHi's identifier for HTML is `html4strict`; DokuWiki
        // normalises `html` for author convenience.
        $this->assertSame(
            ['html4strict', null, null],
            Code::parseAttributes('html')
        );
    }

    function testParseCodeAttributesOptionsOnly()
    {
        $this->assertSame(
            [null, null, ['enable_line_numbers' => true]],
            Code::parseAttributes('[enable_line_numbers]')
        );
    }

    function testParseCodeAttributesLanguageAndOptions()
    {
        $this->assertSame(
            ['php', null, ['enable_line_numbers' => true]],
            Code::parseAttributes('php [enable_line_numbers]')
        );
    }

    function testParseCodeAttributesLanguageFilenameAndOptions()
    {
        $this->assertSame(
            ['php', 'myfile.php', ['enable_line_numbers' => true]],
            Code::parseAttributes('php myfile.php [enable_line_numbers]')
        );
    }

    function testParseCodeAttributesUnknownOptionsReturnsNull()
    {
        // Unknown keys in `[...]` are filtered out; the options slot
        // ends up null, same as if no `[...]` block had been present.
        $this->assertSame(
            ['C', null, null],
            Code::parseAttributes('C [unknown="ignored"]')
        );
    }
}
