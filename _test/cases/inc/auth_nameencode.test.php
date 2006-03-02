<?php

require_once DOKU_INC.'inc/init.php';
require_once DOKU_INC.'inc/auth.php';

class auth_nameencode_test extends UnitTestCase {

    function test_simple(){
        $in  = 'hey$you';
        $out = 'hey%24you';
        $this->assertEqual(auth_nameencode($in),$out);
    }

    function test_complex(){
        $in  = 'hey $ you !$%! foo ';
        $out = 'hey%20%24%20you%20%21%24%25%21%20foo%20';
        $this->assertEqual(auth_nameencode($in),$out);
    }

    function test_complexutf8(){
        $in  = 'häü $ yü !$%! foo ';
        $out = 'häü%20%24%20yü%20%21%24%25%21%20foo%20';
        $this->assertEqual(auth_nameencode($in),$out);
    }

}

//Setup VIM: ex: et ts=4 enc=utf-8 :
