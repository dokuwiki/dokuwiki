<?php
require_once 'parser.inc.php';

/**
 * Tests for the implementation of audio and video files
 *
 * @author  Michael Große <grosse@cosmocode.de>
*/
class TestOfDoku_Parser_Media extends TestOfDoku_Parser {

    function testVideoOGVExternal() {
        $file = 'http://some.where.far/away.ogv';
        $parser_response = p_get_instructions('{{' . $file . '}}');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('externalmedia',array($file,null,null,null,null,'cache','details')),
            array('cdata',array(null)),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$parser_response),$calls);

        $Renderer = new Doku_Renderer_xhtml();
        $url = $Renderer->externalmedia($file,null,null,null,null,'cache','details',true);
        //print_r("url: " . $url);
        $video = '<video class="media" width="320" height="240" controls="controls">';
        $this->assertEquals(substr($url,0,66),$video);
        $source = '<source src="http://some.where.far/away.ogv" type="video/ogg" />';
        $this->assertEquals(substr($url,67,64),$source);
        // work around random token
        $a_first_part = '<a href="/./lib/exe/fetch.php?cache=&amp;tok=';
        $a_second_part = '&amp;media=http%3A%2F%2Fsome.where.far%2Faway.ogv" class="media mediafile mf_ogv" title="http://some.where.far/away.ogv">';
        $this->assertEquals(substr($url,132,45),$a_first_part);
        $this->assertEquals(substr($url,183,121),$a_second_part);
        $rest = 'away.ogv</a></video>'."\n";
        $this->assertEquals(substr($url,304),$rest);
    }

    /**
     * unknown extension of external media file
     */
    function testVideoVIDExternal() {
        $file = 'http://some.where.far/away.vid';
        $parser_response = p_get_instructions('{{' . $file . '}}');

        $calls = array(
            array('document_start', array()),
            array('p_open', array()),
            array('externalmedia', array($file, null, null, null, null, 'cache', 'details')),
            array('cdata', array(null)),
            array('p_close', array()),
            array('document_end', array()),
        );
        $this->assertEquals(array_map('stripbyteindex', $parser_response), $calls);

        $Renderer = new Doku_Renderer_xhtml();
        $url = $Renderer->externalmedia($file, null, null, null, null, 'cache', 'details', true);
        // work around random token
        $a_first_part = '<a href="/./lib/exe/fetch.php?tok=';
        $a_second_part = '&amp;media=http%3A%2F%2Fsome.where.far%2Faway.vid" class="media mediafile mf_vid" title="http://some.where.far/away.vid">';
        $this->assertEquals(substr($url,0,34),$a_first_part);
        $this->assertEquals(substr($url,40,121),$a_second_part);
        $rest = 'away.vid</a>';
        $this->assertEquals(substr($url,161),$rest);
    }


    function testVideoOGVInternal() {
        $file = 'wiki:kind_zu_katze.ogv';
        $parser_response = p_get_instructions('{{' . $file . '}}');

        $calls = array (
            array('document_start',array()),
            array('p_open',array()),
            array('internalmedia',array($file,null,null,null,null,'cache','details')),
            array('cdata',array(null)),
            array('p_close',array()),
            array('document_end',array()),
        );
        $this->assertEquals(array_map('stripbyteindex',$parser_response),$calls);

        $Renderer = new Doku_Renderer_xhtml();
        $url = $Renderer->externalmedia($file,null,null,null,null,'cache','details',true);

        $video = '<video class="media" width="320" height="240" controls="controls" poster="/./lib/exe/fetch.php?media=wiki:kind_zu_katze.png">';
        $this->assertEquals(substr($url,0,125),$video);

        $source_webm = '<source src="/./lib/exe/fetch.php?media=wiki:kind_zu_katze.webm" type="video/webm" />';
        $this->assertEquals(substr($url,126,85),$source_webm);
        $source_ogv = '<source src="/./lib/exe/fetch.php?media=wiki:kind_zu_katze.ogv" type="video/ogg" />';
        $this->assertEquals(substr($url,212,83),$source_ogv);

        $a_webm = '<a href="/./lib/exe/fetch.php?id=&amp;cache=&amp;media=wiki:kind_zu_katze.webm" class="media mediafile mf_webm" title="wiki:kind_zu_katze.webm (99.1 KB)">kind_zu_katze.webm</a>';
        $a_ogv = '<a href="/./lib/exe/fetch.php?id=&amp;cache=&amp;media=wiki:kind_zu_katze.ogv" class="media mediafile mf_ogv" title="wiki:kind_zu_katze.ogv (44.8 KB)">kind_zu_katze.ogv</a>';
        $this->assertEquals(substr($url,296,176),$a_webm);
        $this->assertEquals(substr($url,472,172),$a_ogv);

        $rest = '</video>'."\n";
        $this->assertEquals(substr($url,644),$rest);
    }
}
