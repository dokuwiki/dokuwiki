<?php

class auth_password_test extends DokuWikiTest {

    // hashes for the password foo$method, using abcdefgh as salt
    var $passes = array(
        'smd5'  => '$1$abcdefgh$SYbjm2AEvSoHG7Xapi8so.',
        'apr1'  => '$apr1$abcdefgh$C/GzYTF4kOVByYLEoD5X4.',
        'md5'   => '8fa22d62408e5351553acdd91c6b7003',
        'sha1'  => 'b456d3b0efd105d613744ffd549514ecafcfc7e1',
        'ssha'  => '{SSHA}QMHG+uC7bHNYKkmoLbNsNI38/dJhYmNk',
        'lsmd5' => '{SMD5}HGbkPrkWgy9KgcRGWlrsUWFiY2RlZmdo',
        'crypt' => 'ablvoGr1hvZ5k',
        'mysql' => '4a1fa3780bd6fd55',
        'my411' => '*e5929347e25f82e19e4ebe92f1dc6b6e7c2dbd29',
        'kmd5'  => 'a579299436d7969791189acadd86fcb716',
        'djangomd5'  => 'md5$abcde$d0fdddeda8cd92725d2b54148ac09158',
        'djangosha1' => 'sha1$abcde$c8e65a7f0acc9158843048a53dcc5a6bc4d17678',
        'sha512' => '$6$abcdefgh12345678$J9.zOcgx0lotwZdcz0uulA3IVQMinZvFZVjA5vapRLVAAqtay23XD4xeeUxQ3B4JvDWYFBIxVWW1tOYlHX13k1'
    );


    function test_cryptPassword(){
        foreach($this->passes as $method => $hash){
            $info = "testing method $method";
            $this->assertEquals(auth_cryptPassword('foo'.$method, $method,'abcdefgh12345678912345678912345678'),
                $hash, $info);
        }
    }

    function test_verifyPassword(){
        foreach($this->passes as $method => $hash){
            $info = "testing method $method";
            $this->assertTrue(auth_verifyPassword('foo'.$method, $hash), $info);
            $this->assertFalse(auth_verifyPassword('bar'.$method, $hash), $info);
        }
    }

    function test_verifySelf(){
        foreach($this->passes as $method => $hash){
            $info = "testing method $method";
            $hash = auth_cryptPassword('foo'.$method,$method);
            $this->assertTrue(auth_verifyPassword('foo'.$method, $hash), $info);
        }
    }

    function test_bcrypt_self(){
        $hash = auth_cryptPassword('foobcrypt','bcrypt');
        $this->assertTrue(auth_verifyPassword('foobcrypt',$hash));
    }

    function test_verifyPassword_fixedbcrypt(){
        $this->assertTrue(auth_verifyPassword('foobcrypt','$2a$12$uTWercxbq4sjp2xAzv3we.ZOxk51m5V/Bv5bp2H27oVFJl5neFQoC'));
    }

    function test_verifyPassword_nohash(){
        $this->assertTrue(auth_verifyPassword('foo','$1$$n1rTiFE0nRifwV/43bVon/'));
    }

    function test_verifyPassword_fixedpmd5(){
        $this->assertTrue(auth_verifyPassword('test12345','$P$9IQRaTwmfeRo7ud9Fh4E2PdI0S3r.L0'));
        $this->assertTrue(auth_verifyPassword('test12345','$H$9IQRaTwmfeRo7ud9Fh4E2PdI0S3r.L0'));
    }

    function test_veryPassword_mediawiki(){
        $this->assertTrue(auth_verifyPassword('password', ':B:838c83e1:e4ab7024509eef084cdabd03d8b2972c'));
    }


    /**
     * pmd5 checking should throw an exception when a hash with a too high
     * iteration count is passed
     */
    function test_verifyPassword_pmd5Exception(){
        $except = false;
        try{
            auth_verifyPassword('foopmd5', '$H$abcdefgh1ZbJodHxmeXVAhEzTG7IAp.');
        }catch (Exception $e){
            $except = true;
        }
        $this->assertTrue($except);
    }

}

//Setup VIM: ex: et ts=4 :
