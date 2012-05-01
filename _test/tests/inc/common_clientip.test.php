<?php

class common_clientIP_test extends DokuWikiTest {

    function test_simple_all(){
        $_SERVER['REMOTE_ADDR']          = '123.123.123.123';
        $_SERVER['HTTP_X_REAL_IP']       = '';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '';
        $out = '123.123.123.123';
        $this->assertEquals(clientIP(),$out);
    }

    function test_proxy1_all(){
        $_SERVER['REMOTE_ADDR']          = '123.123.123.123';
        $_SERVER['HTTP_X_REAL_IP']       = '77.77.77.77';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '';
        $out = '123.123.123.123,77.77.77.77';
        $this->assertEquals(clientIP(),$out);
    }

    function test_proxy2_all(){
        $_SERVER['REMOTE_ADDR']          = '123.123.123.123';
        $_SERVER['HTTP_X_REAL_IP']       = '';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '77.77.77.77';
        $out = '123.123.123.123,77.77.77.77';
        $this->assertEquals(clientIP(),$out);
    }

    function test_proxyhops_all(){
        $_SERVER['REMOTE_ADDR']          = '123.123.123.123';
        $_SERVER['HTTP_X_REAL_IP']       = '';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '77.77.77.77,66.66.66.66';
        $out = '123.123.123.123,77.77.77.77,66.66.66.66';
        $this->assertEquals(clientIP(),$out);
    }

    function test_simple_single(){
        $_SERVER['REMOTE_ADDR']          = '123.123.123.123';
        $_SERVER['HTTP_X_REAL_IP']       = '';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '';
        $out = '123.123.123.123';
        $this->assertEquals(clientIP(true),$out);
    }

    function test_proxy1_single(){
        $_SERVER['REMOTE_ADDR']          = '123.123.123.123';
        $_SERVER['HTTP_X_REAL_IP']       = '77.77.77.77';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '';
        $out = '77.77.77.77';
        $this->assertEquals(clientIP(true),$out);
    }

    function test_proxy2_single(){
        $_SERVER['REMOTE_ADDR']          = '123.123.123.123';
        $_SERVER['HTTP_X_REAL_IP']       = '';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '77.77.77.77';
        $out = '77.77.77.77';
        $this->assertEquals(clientIP(true),$out);
    }

    function test_proxyhops_single(){
        $_SERVER['REMOTE_ADDR']          = '123.123.123.123';
        $_SERVER['HTTP_X_REAL_IP']       = '';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '77.77.77.77,66.66.66.66';
        $out = '66.66.66.66';
        $this->assertEquals(clientIP(true),$out);
    }

    function test_local_all(){
        $_SERVER['REMOTE_ADDR']          = '123.123.123.123';
        $_SERVER['HTTP_X_REAL_IP']       = '';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '127.0.0.1';
        $out = '123.123.123.123,127.0.0.1';
        $this->assertEquals(clientIP(),$out);
    }

    function test_local1_single(){
        $_SERVER['REMOTE_ADDR']          = '123.123.123.123';
        $_SERVER['HTTP_X_REAL_IP']       = '';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '127.0.0.1';
        $out = '123.123.123.123';
        $this->assertEquals(clientIP(true),$out);
    }

    function test_local2_single(){
        $_SERVER['REMOTE_ADDR']          = '127.0.0.1';
        $_SERVER['HTTP_X_REAL_IP']       = '';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '123.123.123.123';
        $out = '123.123.123.123';
        $this->assertEquals(clientIP(true),$out);
    }

    function test_local3_single(){
        $_SERVER['REMOTE_ADDR']          = '123.123.123.123';
        $_SERVER['HTTP_X_REAL_IP']       = '';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '127.0.0.1,10.0.0.1,192.168.0.2,172.17.1.1,172.21.1.1,172.31.1.1';
        $out = '123.123.123.123';
        $this->assertEquals(clientIP(true),$out);
    }

    function test_local4_single(){
        $_SERVER['REMOTE_ADDR']          = '127.0.0.1';
        $_SERVER['HTTP_X_REAL_IP']       = '';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '192.168.0.5';
        $out = '192.168.0.5';
        $this->assertEquals(clientIP(true),$out);
    }

    function test_garbage_all(){
        $_SERVER['REMOTE_ADDR']          = '123.123.123.123';
        $_SERVER['HTTP_X_REAL_IP']       = '';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = 'some garbage, or something, 222';
        $out = '123.123.123.123';
        $this->assertEquals(clientIP(),$out);
    }

    function test_garbage_single(){
        $_SERVER['REMOTE_ADDR']          = '123.123.123.123';
        $_SERVER['HTTP_X_REAL_IP']       = '';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = 'some garbage, or something, 222';
        $out = '123.123.123.123';
        $this->assertEquals(clientIP(true),$out);
    }

    function test_garbageonly_all(){
        $_SERVER['REMOTE_ADDR']          = 'argh';
        $_SERVER['HTTP_X_REAL_IP']       = '';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = 'some garbage, or something, 222';
        $out = '0.0.0.0';
        $this->assertEquals(clientIP(),$out);
    }

    function test_garbageonly_single(){
        $_SERVER['REMOTE_ADDR']          = 'argh';
        $_SERVER['HTTP_X_REAL_IP']       = '';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = 'some garbage, or something, 222';
        $out = '0.0.0.0';
        $this->assertEquals(clientIP(true),$out);
    }

    function test_malicious(){
        $_SERVER['REMOTE_ADDR']          = '';
        $_SERVER['HTTP_X_REAL_IP']       = '';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '<?php set_time_limit(0);echo \'my_delim\';passthru(123.123.123.123);die;?>';
        $out = '0.0.0.0';
        $this->assertEquals(clientIP(),$out);
    }

}

//Setup VIM: ex: et ts=4 :
