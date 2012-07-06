<?php

// use no mbstring help here
if(!defined('UTF8_NOMBSTRING')) define('UTF8_NOMBSTRING',1);

class utf8_utf16be_test extends DokuWikiTest {
    // some chars from various code regions
    var $utf8  = '鈩ℵŁöx';
    var $utf16 = "\x92\x29\x21\x35\x1\x41\x0\xf6\x0\x78";

    /**
     * Convert from UTF-8 to UTF-16BE
     */
    function test_to16be(){
        $this->assertEquals(utf8_to_utf16be($this->utf8), $this->utf16);
    }

    /**
     * Convert from UTF-16BE to UTF-8
     */
    function test_from16be(){
        $this->assertEquals(utf16be_to_utf8($this->utf16),$this->utf8);
    }
}

//Setup VIM: ex: et ts=2 :
