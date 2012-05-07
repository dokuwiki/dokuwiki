<?php
// use no mbstring help here
if(!defined('UTF8_NOMBSTRING')) define('UTF8_NOMBSTRING',1);

class utf8_correctidx_test extends DokuWikiTest {


    function test_singlebyte(){
        // we test multiple cases here - format: in, offset, length, out
        $tests   = array();

        // single byte, should return current index
        $tests[] = array('aaживπά우리をあöä',0,false,0);
        $tests[] = array('aaживπά우리をあöä',1,false,1);
        $tests[] = array('aaживπά우리をあöä',1,true,1);

        foreach($tests as $test){
            $this->assertEquals(utf8_correctIdx($test[0],$test[1],$test[2]),$test[3]);
        }
    }

    function test_twobyte(){
        // we test multiple cases here - format: in, offset, length, out
        $tests   = array();

        // two byte, should move to boundary, expect even number
        $tests[] = array('aaживπά우리をあöä',2,false,2);
        $tests[] = array('aaживπά우리をあöä',3,false,2);
        $tests[] = array('aaживπά우리をあöä',4,false,4);

        $tests[] = array('aaживπά우리をあöä',2,true,2);
        $tests[] = array('aaживπά우리をあöä',3,true,4);
        $tests[] = array('aaживπά우리をあöä',4,true,4);

        foreach($tests as $test){
            $this->assertEquals(utf8_correctIdx($test[0],$test[1],$test[2]),$test[3]);
        }
    }

    function test_threebyte(){
        // we test multiple cases here - format: in, offset, length, out
        $tests   = array();

        // three byte, should move to boundary 10 or 13
        $tests[] = array('aaживπά우리をあöä',10,false,10);
        $tests[] = array('aaживπά우리をあöä',11,false,10);
        $tests[] = array('aaживπά우리をあöä',12,false,10);
        $tests[] = array('aaживπά우리をあöä',13,false,13);

        $tests[] = array('aaживπά우리をあöä',10,true,10);
        $tests[] = array('aaживπά우리をあöä',11,true,13);
        $tests[] = array('aaживπά우리をあöä',12,true,13);
        $tests[] = array('aaживπά우리をあöä',13,true,13);

        foreach($tests as $test){
            $this->assertEquals(utf8_correctIdx($test[0],$test[1],$test[2]),$test[3]);
        }
    }

    function test_bounds(){
        // we test multiple cases here - format: in, offset, length, out
        $tests   = array();

        // bounds checking
        $tests[] = array('aaживπά우리をあöä',-2,false,0);
        $tests[] = array('aaживπά우리をあöä',128,false,29);

        $tests[] = array('aaживπά우리をあöä',-2,true,0);
        $tests[] = array('aaживπά우리をあöä',128,true,29);

        foreach($tests as $test){
            $this->assertEquals(utf8_correctIdx($test[0],$test[1],$test[2]),$test[3]);
        }
    }

}
//Setup VIM: ex: et ts=4 :
