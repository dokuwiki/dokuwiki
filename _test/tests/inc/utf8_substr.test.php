<?php
// use no mbstring help here
if(!defined('UTF8_NOMBSTRING')) define('UTF8_NOMBSTRING',1);

class utf8_substr_test extends DokuWikiTest {


    function test1(){
        // we test multiple cases here - format: in, offset, length, out
        $tests   = array();
        $tests[] = array('asciistring',2,null,'ciistring');
        $tests[] = array('asciistring',2,4,'ciis');
        $tests[] = array('asciistring',-4,null,'ring');
        $tests[] = array('asciistring',2,-4,'ciist');
        $tests[] = array('asciistring',-6,-2,'stri');

        $tests[] = array('живπά우리をあöä',2,null,'вπά우리をあöä');
        $tests[] = array('живπά우리をあöä',2,4,'вπά우');
        $tests[] = array('живπά우리をあöä',-4,null,'をあöä');
        $tests[] = array('живπά우리をあöä',2,-4,'вπά우리');
        $tests[] = array('живπά우리をあöä',-6,-2,'우리をあ');

        foreach($tests as $test){
            $this->assertEquals(utf8_substr($test[0],$test[1],$test[2]),$test[3]);
        }
    }

    function test2_bug891() {
        // we test multiple cases here - format: in, offset, length, out
        $tests   = array();

        $str = str_repeat('в',66000).'@@';
        $tests[] = array($str, 65600, 1, 'в');
        $tests[] = array($str,0,66002,$str);

        foreach($tests as $test){
            $this->assertEquals(utf8_substr($test[0],$test[1],$test[2]),$test[3]);
        }
    }

}
//Setup VIM: ex: et ts=4 :
