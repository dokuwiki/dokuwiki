<?php

namespace dokuwiki\test\Parsing\ParserMode;

use Doku_Renderer_xhtml;
use dokuwiki\Parsing\ParserMode\Media;

/**
 * Tests for the {@see Media} parser mode: `{{...}}` media embeds.
 *
 * Covers the parser-level dispatch (internalmedia/externalmedia calls with the right argument tuple),
 * audio/video rendering through the XHTML renderer, and various title / alignment / cache flag cases.
 *
 * @group parser_media
 * @author  Michael Große <grosse@cosmocode.de>
*/
class MediaTest extends ParserTestBase
{
    function testInternal() {
        $this->P->addMode('media', new Media());
        $this->P->parse('Foo {{img.gif}} Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['internalmedia', ['img.gif', null, null, null, null, 'cache', 'details']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testInternalLinkOnly() {
        $this->P->addMode('media', new Media());
        $this->P->parse('Foo {{img.gif?linkonly}} Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['internalmedia', ['img.gif', null, null, null, null, 'cache', 'linkonly']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testNotImage() {
        $this->P->addMode('media', new Media());
        $this->P->parse('Foo {{foo.txt?10x10|Some File}} Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['internalmedia', ['foo.txt', 'Some File', null, 10, 10, 'cache', 'details']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testInternalLAlign() {
        $this->P->addMode('media', new Media());
        $this->P->parse('Foo {{img.gif }} Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['internalmedia', ['img.gif', null, 'left', null, null, 'cache', 'details']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testInternalRAlign() {
        $this->P->addMode('media', new Media());
        $this->P->parse('Foo {{ img.gif}} Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['internalmedia', ['img.gif', null, 'right', null, null, 'cache', 'details']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testInternalCenter() {
        $this->P->addMode('media', new Media());
        $this->P->parse('Foo {{ img.gif }} Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['internalmedia', ['img.gif', null, 'center', null, null, 'cache', 'details']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testInternalParams() {
        $this->P->addMode('media', new Media());
        $this->P->parse('Foo {{img.gif?50x100nocache}} Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['internalmedia', ['img.gif', null, null, '50', '100', 'nocache', 'details']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testInternalTitle() {
        $this->P->addMode('media', new Media());
        $this->P->parse('Foo {{img.gif?50x100|Some Image}} Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['internalmedia', ['img.gif', 'Some Image', null, '50', '100', 'cache', 'details']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testExternal() {
        $this->P->addMode('media', new Media());
        $this->P->parse('Foo {{http://www.google.com/img.gif}} Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['externalmedia', ['http://www.google.com/img.gif', null, null, null, null, 'cache', 'details']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testExternalParams() {
        $this->P->addMode('media', new Media());
        $this->P->parse('Foo {{http://www.google.com/img.gif?50x100nocache}} Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['externalmedia',
                ['http://www.google.com/img.gif', null, null, '50', '100', 'nocache', 'details']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testExternalTitle() {
        $this->P->addMode('media', new Media());
        $this->P->parse('Foo {{http://www.google.com/img.gif?50x100|Some Image}} Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['externalmedia',
                ['http://www.google.com/img.gif', 'Some Image', null, '50', '100', 'cache', 'details']],
            ['cdata', [' Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }

    function testNested() {
        $this->P->addMode('media', new Media());
        $this->P->parse('Foo {{img.gif|{{foo.gif|{{bar.gif|Bar}}}}}} Bar');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['cdata', ["\n" . 'Foo ']],
            ['internalmedia',
                ['img.gif', '{{foo.gif|{{bar.gif|Bar', null, null, null, 'cache', 'details']],
            ['cdata', ['}}}} Bar']],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $this->H->calls);
    }


    function testVideoOGVExternal() {
        $file = 'http://some.where.far/away.ogv';
        $parser_response = p_get_instructions('{{' . $file . '}}');

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['externalmedia',[$file,null,null,null,null,'cache','details']],
            ['cdata',[null]],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $parser_response);

        $Renderer = new Doku_Renderer_xhtml();
        $url = $Renderer->externalmedia($file,null,null,null,null,'cache','details',true);
        //print_r("url: " . $url);
        $video = '<video class="media" width="320" height="240" controls="controls">';
        $this->assertEquals($video, substr($url,0,66));
        $source = '<source src="http://some.where.far/away.ogv" type="video/ogg" />';
        $this->assertEquals($source, substr($url,67,64));
        // work around random token
        $a_first_part = '<a href="' . \DOKU_BASE . 'lib/exe/fetch.php?tok=';
        $a_second_part = '&amp;media=http%3A%2F%2Fsome.where.far%2Faway.ogv" class="media mediafile mf_ogv" title="http://some.where.far/away.ogv">';

        $substr_start = 132;
        $substr_len = strlen($a_first_part);
        $this->assertEquals($a_first_part, substr($url, $substr_start, $substr_len));

        $substr_start = strpos($url, '&amp;media', $substr_start + $substr_len);
        $this->assertNotSame(false, $substr_start, 'Substring not found.');
        $substr_len = strlen($a_second_part);
        $this->assertEquals($a_second_part, substr($url, $substr_start, $substr_len));

        $rest = 'away.ogv</a></video>'."\n";
        $substr_start = strlen($url) - strlen($rest);
        $this->assertEquals($rest, substr($url, $substr_start));
    }

    /**
     * unknown extension of external media file
     */
    function testVideoVIDExternal() {
        $file = 'http://some.where.far/away.vid';
        $parser_response = p_get_instructions('{{' . $file . '}}');

        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['externalmedia', [$file, null, null, null, null, 'cache', 'details']],
            ['cdata', [null]],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $parser_response);

        $Renderer = new Doku_Renderer_xhtml();
        $url = $Renderer->externalmedia($file, null, null, null, null, 'cache', 'details', true);
        // work around random token
        $a_first_part = '<a href="' . \DOKU_BASE . 'lib/exe/fetch.php?tok=';
        $a_second_part = '&amp;media=http%3A%2F%2Fsome.where.far%2Faway.vid" class="media mediafile mf_vid" title="http://some.where.far/away.vid">';

        $substr_start = 0;
        $substr_len = strlen($a_first_part);
        $this->assertEquals($a_first_part, substr($url, $substr_start, $substr_len));

        $substr_start = strpos($url, '&amp;media', $substr_start + $substr_len);
        $this->assertNotSame(false, $substr_start, 'Substring not found.');
        $substr_len = strlen($a_second_part);
        $this->assertEquals($a_second_part, substr($url, $substr_start, $substr_len));

        $rest = 'away.vid</a>';
        $substr_start = strlen($url) - strlen($rest);
        $this->assertEquals($rest, substr($url, $substr_start));
    }


    function testVideoOGVInternal() {
        $file = 'wiki:kind_zu_katze.ogv';
        $parser_response = p_get_instructions('{{' . $file . '}}');

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['internalmedia',[$file,null,null,null,null,'cache','details']],
            ['cdata',[null]],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $parser_response);

        $Renderer = new Doku_Renderer_xhtml();
        $url = $Renderer->externalmedia($file,null,null,null,null,'cache','details',true);

        $video = '<video class="media" width="320" height="240" controls="controls" poster="' . \DOKU_BASE . 'lib/exe/fetch.php?media=wiki:kind_zu_katze.png">';
        $substr_start = 0;
        $substr_len = strlen($video);
        $this->assertEquals($video, substr($url, $substr_start, $substr_len));

        // find $source_webm in $url
        $source_webm = '<source src="' . \DOKU_BASE . 'lib/exe/fetch.php?media=wiki:kind_zu_katze.webm" type="video/webm" />';
        $substr_start = strpos($url, $source_webm, $substr_start + $substr_len);
        $this->assertNotSame(false, $substr_start, 'Substring not found.');

        // find $source_ogv in $url
        $source_ogv = '<source src="' . \DOKU_BASE . 'lib/exe/fetch.php?media=wiki:kind_zu_katze.ogv" type="video/ogg" />';
        $substr_start = strpos($url, $source_ogv, $substr_start + strlen($source_webm));
        $this->assertNotSame(false, $substr_start, 'Substring not found.');

        // find $a_webm in $url
        $a_webm = '<a href="' . \DOKU_BASE . 'lib/exe/fetch.php?media=wiki:kind_zu_katze.webm" class="media mediafile mf_webm" title="wiki:kind_zu_katze.webm (99.1'."\xC2\xA0".'KB)">kind_zu_katze.webm</a>';
        $substr_start = strpos($url, $a_webm, $substr_start + strlen($source_ogv));
        $this->assertNotSame(false, $substr_start, 'Substring not found.');

        // find $a_webm in $url
        $a_ogv = '<a href="' . \DOKU_BASE . 'lib/exe/fetch.php?media=wiki:kind_zu_katze.ogv" class="media mediafile mf_ogv" title="wiki:kind_zu_katze.ogv (44.8'."\xC2\xA0".'KB)">kind_zu_katze.ogv</a>';
        $substr_start = strpos($url, $a_ogv, $substr_start + strlen($a_webm));
        $this->assertNotSame(false, $substr_start, 'Substring not found.');

        $rest = '</video>'."\n";
        $substr_start = strlen($url) - strlen($rest);
        $this->assertEquals($rest, substr($url, $substr_start));
    }

    function testVideoInternalTitle() {
        $file = 'wiki:kind_zu_katze.ogv';
        $title = 'Single quote: \' Ampersand: &';

        $Renderer = new Doku_Renderer_xhtml();
        $url = $Renderer->externalmedia($file, $title, null, null, null, 'cache', 'details', true);

        // make sure the title is escaped just once
        $this->assertEquals(hsc($title), substr($url, 28, 37));
    }

    function testSimpleLinkText() {
        $file = 'wiki:dokuwiki-128.png';
        $parser_response = p_get_instructions('{{' . $file . '|This is a simple text.}}');

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['internalmedia',[$file,'This is a simple text.',null,null,null,'cache','details']],
            ['cdata',[null]],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $parser_response);
    }

    function testLinkTextWithWavedBrackets_1() {
        $file = 'wiki:dokuwiki-128.png';
        $parser_response = p_get_instructions('{{' . $file . '|We got a { here.}}');

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['internalmedia',[$file,'We got a { here.',null,null,null,'cache','details']],
            ['cdata',[null]],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $parser_response);
    }

    function testLinkTextWithWavedBrackets_2() {
        $file = 'wiki:dokuwiki-128.png';
        $parser_response = p_get_instructions('{{' . $file . '|We got a } here.}}');

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['internalmedia',[$file,'We got a } here.',null,null,null,'cache','details']],
            ['cdata',[null]],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $parser_response);
    }

    function testAlignFromWhitespace() {
        // DW's historical whitespace-inside-braces alignment still works.
        $file = 'wiki:image.png';
        $parser_response = p_get_instructions('{{ ' . $file . '}}');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['internalmedia', [$file, null, 'right', null, null, 'cache', 'details']],
            ['cdata', [null]],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $parser_response);
    }

    function testAlignFromParameter() {
        $file = 'wiki:image.png';
        $parser_response = p_get_instructions('{{' . $file . '?left}}');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['internalmedia', [$file, null, 'left', null, null, 'cache', 'details']],
            ['cdata', [null]],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $parser_response);
    }

    function testAlignParameterBeatsWhitespace() {
        // Explicit ?center wins over whitespace-derived 'left' (trailing space).
        $file = 'wiki:image.png';
        $parser_response = p_get_instructions('{{' . $file . '?center }}');
        $calls = [
            ['document_start', []],
            ['p_open', []],
            ['internalmedia', [$file, null, 'center', null, null, 'cache', 'details']],
            ['cdata', [null]],
            ['p_close', []],
            ['document_end', []],
        ];
        $this->assertCalls($calls, $parser_response);
    }

    function testLinkTextWithWavedBrackets_3() {
        $file = 'wiki:dokuwiki-128.png';
        $parser_response = p_get_instructions('{{' . $file . '|We got a { and a } here.}}');

        $calls = [
            ['document_start',[]],
            ['p_open',[]],
            ['internalmedia',[$file,'We got a { and a } here.',null,null,null,'cache','details']],
            ['cdata',[null]],
            ['p_close',[]],
            ['document_end',[]],
        ];
        $this->assertCalls($calls, $parser_response);
    }
}
