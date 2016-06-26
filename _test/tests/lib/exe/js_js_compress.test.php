<?php

require_once DOKU_INC.'lib/exe/js.php';

class js_js_compress_test extends DokuWikiTest {

    function test_mlcom1(){
        $text = '/**
                  * A multi
                  * line *test*
                  * check
                  */';
        $this->assertEquals(js_compress($text), '');
    }

    function test_mlcom2(){
        $text = 'var foo=6;/* another comment */';
        $this->assertEquals(js_compress($text), 'var foo=6;');
    }

    function test_mlcomcond(){
        $text = '/*@if (@_win32)';
        $this->assertEquals(js_compress($text), '/*@if(@_win32)');
    }

    function test_slcom1(){
        $text = '// an comment';
        $this->assertEquals(js_compress($text), '');
    }

    function test_slcom2(){
        $text = 'var foo=6;// another comment ';
        $this->assertEquals(js_compress($text), 'var foo=6;');
    }

    function test_slcom3(){
        $text = 'var foo=6;// another comment / or something with // comments ';
        $this->assertEquals(js_compress($text), 'var foo=6;');
    }

    function test_regex1(){
        $text = 'foo.split( /[a-Z\/]*/ );';
        $this->assertEquals(js_compress($text), 'foo.split(/[a-Z\/]*/);');
    }

    function test_regex_in_array(){
        $text = '[/"/ , /"/ , /"/]';
        $this->assertEquals(js_compress($text), '[/"/,/"/,/"/]');
    }

    function test_regex_in_hash(){
        $text = '{ a : /"/ }';
        $this->assertEquals(js_compress($text), '{a:/"/}');
    }

    function test_regex_preceded_by_spaces_caracters(){
        $text = "text.replace( \t \r\n  /\"/ , ".'"//" )';
        $this->assertEquals(js_compress($text), 'text.replace(/"/,"//")');
    }

    function test_regex_after_and_with_slashes_outside_string(){
        $text = 'if ( peng == bla && /pattern\//.test(url)) request = new Something();';
        $this->assertEquals(js_compress($text),
                            'if(peng==bla&&/pattern\//.test(url))request=new Something();');
    }

    function test_regex_after_or_with_slashes_outside_string(){
        $text = 'if ( peng == bla || /pattern\//.test(url)) request = new Something();';
        $this->assertEquals(js_compress($text),
                            'if(peng==bla||/pattern\//.test(url))request=new Something();');
    }

    function test_dquot1(){
        $text = 'var foo="Now what \\" \'do we//get /*here*/ ?";';
        $this->assertEquals(js_compress($text), $text);
    }

    function test_dquot2(){
        $text = 'var foo="Now what \\\\\\" \'do we//get /*here*/ ?";';
        $this->assertEquals(js_compress($text), $text);
    }

    function test_dquotrunaway(){
        $text = 'var foo="Now where does it end';
        $this->assertEquals(js_compress($text), $text);
    }

    function test_squot1(){
        $text = "var foo='Now what \\' \"do we//get /*here*/ ?';";
        $this->assertEquals(js_compress($text), $text);
    }

    function test_squotrunaway(){
        $text = "var foo='Now where does it end";
        $this->assertEquals(js_compress($text), $text);
    }

    function test_nl1(){
        $text = "var foo=6;\nvar baz=7;";
        $this->assertEquals(js_compress($text), 'var foo=6;var baz=7;');
    }

    function test_lws1(){
        $text = "  \t  var foo=6;";
        $this->assertEquals(js_compress($text), 'var foo=6;');
    }

    function test_tws1(){
        $text = "var foo=6;  \t  ";
        $this->assertEquals(js_compress($text), 'var foo=6;');
    }

    function test_shortcond(){
        $text = "var foo = (baz) ? 'bar' : 'bla';";
        $this->assertEquals(js_compress($text), "var foo=(baz)?'bar':'bla';");

    }

    function test_complexminified(){
        $text = 'if(!k.isXML(a))try{if(e||!l.match.PSEUDO.test(c)&&!/!=/.test(c)){var f=b.call(a,c);if(f||!d||a.document&&a.document.nodeType!==11)return f}}catch(g){}return k(c,null,null,[a]).length>0}}}(),function(){var a=c.createElement("div");a.innerHTML="<div class=\'test e\'></div><div class=\'test\'></div>";if(!!a.getElementsByClassName&&a.getElementsByClassName("e").length!==0){a.lastChild.className="e";if(a.getElementsByClassName("e").length===1)return;foo="text/*";bla="*/"';

        $this->assertEquals(js_compress($text),$text);
    }

    function test_multilinestring(){
        $text = 'var foo = "this is a \\'."\n".'multiline string";';
        $this->assertEquals('var foo="this is a multiline string";',js_compress($text));

        $text = "var foo = 'this is a \\\nmultiline string';";
        $this->assertEquals("var foo='this is a multiline string';",js_compress($text));
    }

    function test_nocompress(){
        $text = <<<EOF
var meh   =    'test' ;

/* BEGIN NOCOMPRESS */


var foo   =    'test' ;

var bar   =    'test' ;


/* END NOCOMPRESS */

var moh   =    'test' ;
EOF;
        $out = <<<EOF
var meh='test';
var foo   =    'test' ;

var bar   =    'test' ;
var moh='test';
EOF;

        $this->assertEquals($out, js_compress($text));
    }

    function test_plusplus1(){
        $text = 'a = 5 + ++b;';
        $this->assertEquals('a=5+ ++b;',js_compress($text));
    }

    function test_plusplus2(){
        $text = 'a = 5+ ++b;';
        $this->assertEquals('a=5+ ++b;',js_compress($text));
    }

    function test_plusplus3(){
        $text = 'a = 5++ + b;';
        $this->assertEquals('a=5++ +b;',js_compress($text));
    }

    function test_plusplus4(){
        $text = 'a = 5++ +b;';
        $this->assertEquals('a=5++ +b;',js_compress($text));
    }

    function test_minusminus1(){
        $text = 'a = 5 - --b;';
        $this->assertEquals('a=5- --b;',js_compress($text));
    }

    function test_minusminus2(){
        $text = 'a = 5- --b;';
        $this->assertEquals('a=5- --b;',js_compress($text));
    }

    function test_minusminus3(){
        $text = 'a = 5-- - b;';
        $this->assertEquals('a=5-- -b;',js_compress($text));
    }

    function test_minusminus4(){
        $text = 'a = 5-- -b;';
        $this->assertEquals('a=5-- -b;',js_compress($text));
    }

    function test_minusplus1(){
        $text = 'a = 5-- +b;';
        $this->assertEquals('a=5--+b;',js_compress($text));
    }

    function test_minusplus2(){
        $text = 'a = 5-- + b;';
        $this->assertEquals('a=5--+b;',js_compress($text));
    }

    function test_plusminus1(){
        $text = 'a = 5++ - b;';
        $this->assertEquals('a=5++-b;',js_compress($text));
    }

    function test_plusminus2(){
        $text = 'a = 5++ -b;';
        $this->assertEquals('a=5++-b;',js_compress($text));
    }

    function test_unusual_signs(){
        $text='var π = Math.PI, τ = 2 * π, halfπ = π / 2, ε = 1e-6, ε2 = ε * ε, radians = π / 180, degrees = 180 / π;';
        $this->assertEquals(js_compress($text),
                            'var π=Math.PI,τ=2*π,halfπ=π/2,ε=1e-6,ε2=ε*ε,radians=π/180,degrees=180/π;');
    }

    /**
     * Test the files provided with the original JsStrip
     */
    function test_original(){
        $files = glob(dirname(__FILE__).'/js_js_compress/test-*-in.js');

        foreach($files as $file){
            $info = "Using file $file";
            $this->assertEquals(js_compress(file_get_contents($file)),
                               file_get_contents(substr($file,0,-5).'out.js'), $info);
        };
    }
}

//Setup VIM: ex: et ts=4 :
