<?php

class auth_nameencode_test extends DokuWikiTest {

    function teardown() {
        global $cache_authname;
        $cache_authname = array();
    }

    function test_simple(){
        $in  = 'hey$you';
        $out = 'hey%24you';
        $this->assertEquals(auth_nameencode($in),$out);
    }

    function test_quote(){
        $in  = 'hey"you';
        $out = 'hey%22you';
        $this->assertEquals(auth_nameencode($in),$out);
    }

    function test_complex(){
        $in  = 'hey $ you !$%! foo ';
        $out = 'hey%20%24%20you%20%21%24%25%21%20foo%20';
        $this->assertEquals(auth_nameencode($in),$out);
    }

    function test_complexutf8(){
        $in  = 'häü $ yü !$%! foo ';
        $out = 'häü%20%24%20yü%20%21%24%25%21%20foo%20';
        $this->assertEquals(auth_nameencode($in),$out);
    }

    function test_groupskipon(){
        $in  = '@hey$you';
        $out = '@hey%24you';
        $this->assertEquals(auth_nameencode($in,true),$out);
    }

    function test_groupskipoff(){
        $in  = '@hey$you';
        $out = '%40hey%24you';
        $this->assertEquals(auth_nameencode($in),$out);
    }
}

//Setup VIM: ex: et ts=4 :
