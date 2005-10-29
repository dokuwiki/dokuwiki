<?php

require_once DOKU_INC.'lib/exe/css.php';


class css_css_compress_test extends UnitTestCase {

    function test_mlcom1(){
        $text = '/**
                  * A multi
                  * line *test*
                  * check
                  */';
        $this->assertEqual(css_compress($text), '');
    }

    function test_mlcom2(){
        $text = '#comment/* */ {
                    color: lime;
                }';
        $this->assertEqual(css_compress($text), '#comment/* */{color:lime;}');
    }

    function test_nl1(){
        $text = "a{left:20px;\ntop:20px}";
        $this->assertEqual(css_compress($text), 'a{left:20px;top:20px}');
    }

}

//Setup VIM: ex: et ts=4 enc=utf-8 :
