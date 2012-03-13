<?php

require_once DOKU_INC.'inc/init.php';
require_once DOKU_INC.'inc/common.php';

class common_clientIP_test extends UnitTestCase {

    function test_unix(){
        $unix = 'one
                two

                three';

        $this->assertEqual($unix,cleanText($unix));
    }

    function test_win(){
        $unix = 'one
                two

                three';
        $win  = 'one
                two
                
                three';
        $this->assertNotEqual($unix,$win);
        $this->assertEqual($unix,cleanText($win));
    }
}

//Setup VIM: ex: et ts=4 :
