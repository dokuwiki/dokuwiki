<?php
// use no mbstring help here
if(!defined('UTF8_NOMBSTRING')) define('UTF8_NOMBSTRING',1);
require_once DOKU_INC.'inc/utf8.php';

class utf8_correctidx_test extends UnitTestCase {


    function test1(){
        // we test multiple cases here - format: in, offset, length, out
        $tests   = array();

        $tests[] = array('живπά우리をあöä',1,false,0);
        $tests[] = array('живπά우리をあöä',2,false,2);
        $tests[] = array('живπά우리をあöä',1,true,2);
        $tests[] = array('живπά우리をあöä',0,false,0);
        $tests[] = array('живπά우리をあöä',2,true,2);

        foreach($tests as $test){
            $this->assertEqual(utf8_correctIdx($test[0],$test[1],$test[2]),$test[3]);
        }
    }

}
//Setup VIM: ex: et ts=4 enc=utf-8 :
