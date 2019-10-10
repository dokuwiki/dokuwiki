<?php

// use no mbstring help here
if(!defined('UTF8_NOMBSTRING')) define('UTF8_NOMBSTRING',1);

class utf8_html_test extends DokuWikiTest {

    function test_from_1byte(){
        $in  = 'a';
        $out = 'a';
        $this->assertEquals(\dokuwiki\Utf8\Conversion::toHtml($in),$out);
    }

    function test_from_1byte_all(){
        $in  = 'a';
        $out = '&#97;';
        $this->assertEquals(utf8_tohtml($in, true),$out);
    }

    function test_from_2byte(){
        $in  = "\xc3\xbc";
        $out = '&#252;';
        $this->assertEquals(\dokuwiki\Utf8\Conversion::toHtml($in),$out);
    }

    function test_from_3byte(){
        $in  = "\xe2\x99\x8a";
        $out = '&#x264a;';
        $this->assertEquals(\dokuwiki\Utf8\Conversion::toHtml($in),$out);
    }

    function test_from_4byte(){
        $in  = "\xf4\x80\x80\x81";
        $out = '&#x100001;';
        $this->assertEquals(\dokuwiki\Utf8\Conversion::toHtml($in),$out);
    }

    function test_to_1byte(){
        $out  = 'a';
        $in = 'a';
        $this->assertEquals(\dokuwiki\Utf8\Conversion::fromHtml($in),$out);
    }

    function test_to_2byte(){
        $out  = "\xc3\xbc";
        $in = '&#252;';
        $this->assertEquals(\dokuwiki\Utf8\Conversion::fromHtml($in),$out);
    }

    function test_to_3byte(){
        $out  = "\xe2\x99\x8a";
        $in = '&#x264a;';
        $this->assertEquals(\dokuwiki\Utf8\Conversion::fromHtml($in),$out);
    }

    function test_to_4byte(){
        $out  = "\xf4\x80\x80\x81";
        $in = '&#x100001;';
        $this->assertEquals(\dokuwiki\Utf8\Conversion::fromHtml($in),$out);
    }

    function test_without_entities(){
        $out  = '&amp;#38;&amp;#38;';
        $in = '&amp;#38;&#38;amp;#38;';
        $this->assertEquals(\dokuwiki\Utf8\Conversion::fromHtml($in),$out);
    }

    function test_with_entities(){
        $out  = '&#38;&amp;#38;';
        $in = '&amp;#38;&#38;amp;#38;';
        $this->assertEquals(\dokuwiki\Utf8\Conversion::fromHtml($in,HTML_ENTITIES),$out);
    }

}

//Setup VIM: ex: et ts=4 :
