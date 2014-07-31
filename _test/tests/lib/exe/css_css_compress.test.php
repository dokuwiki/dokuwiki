<?php

require_once DOKU_INC.'lib/exe/css.php';

class css_css_compress_test extends DokuWikiTest {

    function test_mlcom1(){
        $text = '/**
                  * A multi
                  * line *test*
                  * check
                  */';
        $this->assertEquals('', css_compress($text));
    }

    function test_mlcom2(){
        $text = '#comment/* */ {
                    color: lime;
                }';
        $this->assertEquals('#comment/* */{color:lime;}', css_compress($text));
    }

    function test_slcom1(){
        $text = '// this is a comment';
        $this->assertEquals('', css_compress($text));
    }

    function test_slcom2(){
        $text = '#foo {
                    color: lime; // another comment
                }';
        $this->assertEquals('#foo{color:lime;}', css_compress($text));
    }

    function test_slcom3(){
        $text = '#foo {
                    background-image: url(http://foo.bar/baz.jpg); // this is a comment
                }';
        $this->assertEquals('#foo{background-image:url(http://foo.bar/baz.jpg);}', css_compress($text));
    }

    function test_slcom4(){
        $text = '#foo {
                    background-image: url(http://foo.bar/baz.jpg); background-image: url(http://foo.bar/baz.jpg); // this is a comment
                }';
        $this->assertEquals('#foo{background-image:url(http://foo.bar/baz.jpg);background-image:url(http://foo.bar/baz.jpg);}', css_compress($text));
    }

    function test_slcom5(){
        $text = '#foo {
                    background-image: url(http://foo.bar/baz.jpg); // background-image: url(http://foo.bar/baz.jpg); this is all commented
                }';
        $this->assertEquals('#foo{background-image:url(http://foo.bar/baz.jpg);}', css_compress($text));
    }

    function test_slcom6(){
        $text = '#foo {
                    background-image: url(//foo.bar/baz.jpg); // background-image: url(http://foo.bar/baz.jpg); this is all commented
                }';
        $this->assertEquals('#foo{background-image:url(//foo.bar/baz.jpg);}', css_compress($text));
    }

    function test_slcom7(){
        $text = '#foo a[href ^="https://"], #foo a[href ^=\'https://\'] {
                    background-image: url(//foo.bar/baz.jpg); // background-image: url(http://foo.bar/baz.jpg); this is \'all\' "commented"
                }';
        $this->assertEquals('#foo a[href ^="https://"],#foo a[href ^=\'https://\']{background-image:url(//foo.bar/baz.jpg);}', css_compress($text));
    }


    function test_hack(){
        $text = '/* Mac IE will not see this and continue with inline-block */
                 /* \\*/
                 display: inline; 
                 /* */';
        $this->assertEquals('/* \\*/display:inline;/* */', css_compress($text));
    }

    function test_hack2(){
        $text = '/* min-height hack for Internet Explorer http://www.cssplay.co.uk/boxes/minheight.html */
                 /*\\*/
                 * html .page {
                     height: 450px;
                 }
                 /**/';
        $this->assertEquals('/*\\*/* html .page{height:450px;}/**/', css_compress($text));
    }

    function test_nl1(){
        $text = "a{left:20px;\ntop:20px}";
        $this->assertEquals('a{left:20px;top:20px}', css_compress($text));
    }

    function test_shortening() {
        $input = array(
            'margin:0em 0em 0em 0em ul.test margin:0em :0em div#FFFFFF {',
            'margin:  1px 1px 1px 1px;',
            'padding: 1px 2px 1px 2px;',
            'margin:  1px 2px 3px 1px;',
            'padding: 1px 2px 3px 4px;',
            'margin:  00.00em 0em 01.00px 0em;',
            'padding: 0010em 0010.00em 00.00em 00.00100em;',
            'padding: 0010% 0010.00% 00.00% 00.00100xxx;',
            'padding: 0.0em .0em 0.em 00.00em;',
            'padding: 01.0em;',
            'color:   #FFFFFF;',
            'color:   #777777;',
            'color:   #123456;',
            'border:  01.0em solid #ffffff;',
        );

        $expected = array(
            'margin:0em 0em 0em 0em ul.test margin:0em :0em div#FFFFFF{',
            'margin:1px;',
            'padding:1px 2px;',
            'margin:1px 2px 3px 1px;',
            'padding:1px 2px 3px 4px;',
            'margin:0 0 1px 0;',
            'padding:10em 10em 0 .001em;',
            'padding:10% 10% 0 00.00100xxx;',
            'padding:0;',
            'padding:1em;',
            'color:#FFF;',
            'color:#777;',
            'color:#123456;',
            'border:1em solid #fff;',
        );

        $input = array_map('css_compress', $input);

        $this->assertEquals($expected, $input);
    }

    function test_data() {
        $input  = 'list-style-image: url(data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7);';
        $expect = 'list-style-image:url(data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7);';

        $this->assertEquals($expect, css_compress($input));
    }

}

//Setup VIM: ex: et ts=4 :
