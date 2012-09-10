<?php

// use no mbstring help here
if(!defined('UTF8_NOMBSTRING')) define('UTF8_NOMBSTRING',1);

class utf8_unicode_test extends DokuWikiTest {

    function test_from_1byte(){
        $in  = 'a';
        $out = array(97);
        $this->assertEquals(utf8_to_unicode($in),$out);
    }

    function test_from_2byte(){
        $in  = "\xc3\xbc";
        $out = array(252);
        $this->assertEquals(utf8_to_unicode($in),$out);
    }

    function test_from_3byte(){
        $in  = "\xe2\x99\x8a";
        $out = array(9802);
        $this->assertEquals(utf8_to_unicode($in),$out);
    }

    function test_from_4byte(){
        $in  = "\xf4\x80\x80\x81";
        $out = array(1048577);
        $this->assertEquals(utf8_to_unicode($in),$out);
    }

    function test_to_1byte(){
        $out  = 'a';
        $in = array(97);
        $this->assertEquals(unicode_to_utf8($in),$out);
    }

    function test_to_2byte(){
        $out  = "\xc3\xbc";
        $in = array(252);
        $this->assertEquals(unicode_to_utf8($in),$out);
    }

    function test_to_3byte(){
        $out  = "\xe2\x99\x8a";
        $in = array(9802);
        $this->assertEquals(unicode_to_utf8($in),$out);
    }

    function test_to_4byte(){
        $out  = "\xf4\x80\x80\x81";
        $in = array(1048577);
        $this->assertEquals(unicode_to_utf8($in),$out);
    }

}

//Setup VIM: ex: et ts=4 :
