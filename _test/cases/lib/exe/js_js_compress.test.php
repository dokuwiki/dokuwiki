<?php

require_once DOKU_INC.'lib/exe/js.php';


class js_js_compress_test extends UnitTestCase {

    function test_mlcom1(){
        $text = '/**
                  * A multi
                  * line *test*
                  * check
                  */';
        $this->assertEqual(js_compress($text), '');
    }

    function test_mlcom2(){
        $text = 'var foo=6;/* another comment */';
        $this->assertEqual(js_compress($text), 'var foo=6;');
    }

    function test_mlcomcond(){
        $text = '/*@if (@_win32)';
        $this->assertEqual(js_compress($text), '/*@if(@_win32)');
    }

    function test_slcom1(){
        $text = '// an comment';
        $this->assertEqual(js_compress($text), '');
    }

    function test_slcom2(){
        $text = 'var foo=6;// another comment ';
        $this->assertEqual(js_compress($text), 'var foo=6;');
    }

    function test_slcom3(){
        $text = 'var foo=6;// another comment / or something with // comments ';
        $this->assertEqual(js_compress($text), 'var foo=6;');
    }

    function test_regex1(){
        $text = 'foo.split( /[a-Z\/]*/ );';
        $this->assertEqual(js_compress($text), 'foo.split(/[a-Z\/]*/);');
    }

    function test_regex_in_array(){
        $text = '[/"/ , /"/ , /"/]';
        $this->assertEqual(js_compress($text), '[/"/,/"/,/"/]');
    }

    function test_regex_in_hash(){
        $text = '{ a : /"/ }';
        $this->assertEqual(js_compress($text), '{a:/"/}');
    }

    function test_regex_preceded_by_spaces_caracters(){
        $text = "text.replace( \t \r\n  /\"/ , ".'"//" )';
        $this->assertEqual(js_compress($text), 'text.replace(/"/,"//")');
    }

    function test_dquot1(){
        $text = 'var foo="Now what \\" \'do we//get /*here*/ ?";';
        $this->assertEqual(js_compress($text), $text);
    }

    function test_dquot2(){
        $text = 'var foo="Now what \\\\\\" \'do we//get /*here*/ ?";';
        $this->assertEqual(js_compress($text), $text);
    }

    function test_dquotrunaway(){
        $text = 'var foo="Now where does it end';
        $this->assertEqual(js_compress($text), $text);
    }

    function test_squot1(){
        $text = "var foo='Now what \\' \"do we//get /*here*/ ?';";
        $this->assertEqual(js_compress($text), $text);
    }

    function test_squotrunaway(){
        $text = "var foo='Now where does it end";
        $this->assertEqual(js_compress($text), $text);
    }

    function test_nl1(){
        $text = "var foo=6;\nvar baz=7;";
        $this->assertEqual(js_compress($text), 'var foo=6;var baz=7;');
    }

    function test_lws1(){
        $text = "  \t  var foo=6;";
        $this->assertEqual(js_compress($text), 'var foo=6;');
    }

    function test_tws1(){
        $text = "var foo=6;  \t  ";
        $this->assertEqual(js_compress($text), 'var foo=6;');
    }

    function test_shortcond(){
        $text = "var foo = (baz) ? 'bar' : 'bla';";
        $this->assertEqual(js_compress($text), "var foo=(baz)?'bar':'bla';");

    }

    function test_complexminified(){
        $text = 'if(!k.isXML(a))try{if(e||!l.match.PSEUDO.test(c)&&!/!=/.test(c)){var f=b.call(a,c);if(f||!d||a.document&&a.document.nodeType!==11)return f}}catch(g){}return k(c,null,null,[a]).length>0}}}(),function(){var a=c.createElement("div");a.innerHTML="<div class=\'test e\'></div><div class=\'test\'></div>";if(!!a.getElementsByClassName&&a.getElementsByClassName("e").length!==0){a.lastChild.className="e";if(a.getElementsByClassName("e").length===1)return;foo="text/*";bla="*/"';

        $this->assertEqual(js_compress($text),$text);
    }

    /**
     * Test the files provided with the original JsStrip
     */
    function test_original(){
        $files = glob(dirname(__FILE__).'/js_js_compress/test-*-in.js');

        foreach($files as $file){
            $info = "Using file $file";
            $this->signal('failinfo',$info);
            $this->assertEqual(js_compress(file_get_contents($file)),
                               file_get_contents(substr($file,0,-5).'out.js'));
        };
    }
}

//Setup VIM: ex: et ts=4 :
