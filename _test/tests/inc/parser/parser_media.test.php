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
}

/**
 * .oga:
 * http://upload.wikimedia.org/wikipedia/commons/6/6b/Meow_of_a_pleading_cat.oga
 *
 * .wav:
 * http://upload.wikimedia.org/wikipedia/commons/8/81/Meow_of_a_Siamese_cat_-_freemaster2.wav
 *
 *
 */
