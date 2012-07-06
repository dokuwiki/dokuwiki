<?php

class common_cleanText_test extends DokuWikiTest {

    function test_unix(){
        $unix = "one\n                two\n\n                three";
        $this->assertEquals($unix,cleanText($unix));
    }

    function test_win(){
        $unix = "one\ntwo\nthree";
        $win = "one\r\ntwo\r\nthree";

        $this->assertEquals(bin2hex($unix), '6f6e650a74776f0a7468726565');
        $this->assertEquals(bin2hex($win), '6f6e650d0a74776f0d0a7468726565');
        $this->assertNotEquals($unix, $win);
        $this->assertEquals($unix, cleanText($win));
    }
}

//Setup VIM: ex: et ts=4 :
