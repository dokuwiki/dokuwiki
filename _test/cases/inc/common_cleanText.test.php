<?php

require_once DOKU_INC.'inc/init.php';
require_once DOKU_INC.'inc/common.php';

class common_cleanText extends UnitTestCase {

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
        $win = 'one
                two

                three';

        $this->assertEqual(bin2hex($unix),'6f6e650a2020202020202020202020202020202074776f0a0a202020202020202020202020202020207468726565');
        $this->assertEqual(bin2hex($win),'6f6e650d0a2020202020202020202020202020202074776f0d0a0d0a202020202020202020202020202020207468726565');
        $this->assertNotEqual($unix,$win);
        $this->assertEqual($unix,cleanText($win));
    }
}

//Setup VIM: ex: et ts=4 :
