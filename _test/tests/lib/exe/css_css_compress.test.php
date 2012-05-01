<?php

require_once DOKU_INC.'lib/exe/css.php';

class css_css_compress_test extends DokuWikiTest {

    function test_mlcom1(){
        $text = '/**
                  * A multi
                  * line *test*
                  * check
                  */';
        $this->assertEquals(css_compress($text), '');
    }

    function test_mlcom2(){
        $text = '#comment/* */ {
                    color: lime;
                }';
        $this->assertEquals(css_compress($text), '#comment/* */{color:lime;}');
    }

    function test_slcom1(){
        $text = '// this is a comment';
        $this->assertEquals(css_compress($text), '');
    }

    function test_slcom2(){
        $text = '#foo {
                    color: lime; // another comment
                }';
        $this->assertEquals(css_compress($text), '#foo{color:lime;}');
    }

    function test_slcom3(){
        $text = '#foo {
                    background-image: url(http://foo.bar/baz.jpg);
                }';
        $this->assertEquals(css_compress($text), '#foo{background-image:url(http://foo.bar/baz.jpg);}');
    }

    function test_hack(){
        $text = '/* Mac IE will not see this and continue with inline-block */
                 /* \\*/
                 display: inline; 
                 /* */';
        $this->assertEquals(css_compress($text), '/* \\*/display:inline;/* */');
    }

    function test_hack2(){
        $text = '/* min-height hack for Internet Explorer http://www.cssplay.co.uk/boxes/minheight.html */
                 /*\\*/
                 * html .page {
                     height: 450px;
                 }
                 /**/';
        $this->assertEquals(css_compress($text), '/*\\*/* html .page{height:450px;}/**/');
    }

    function test_nl1(){
        $text = "a{left:20px;\ntop:20px}";
        $this->assertEquals(css_compress($text), 'a{left:20px;top:20px}');
    }

}

//Setup VIM: ex: et ts=4 :
