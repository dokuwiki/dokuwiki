<?php

require_once DOKU_INC.'inc/init.php';
require_once DOKU_INC.'inc/auth.php';

class auth_password_test extends UnitTestCase {

    // hashes for the password foo$method, using abcdefgh as salt
    var $passes = array(
        'smd5'  => '$1$abcdefgh$SYbjm2AEvSoHG7Xapi8so.',
        'apr1'  => '$apr1$abcdefgh$C/GzYTF4kOVByYLEoD5X4.',
        'md5'   => '8fa22d62408e5351553acdd91c6b7003',
        'sha1'  => 'b456d3b0efd105d613744ffd549514ecafcfc7e1',
        'ssha'  => '{SSHA}QMHG+uC7bHNYKkmoLbNsNI38/dJhYmNk',
        'crypt' => 'ablvoGr1hvZ5k',
        'mysql' => '4a1fa3780bd6fd55',
        'my411' => '*e5929347e25f82e19e4ebe92f1dc6b6e7c2dbd29',
    );


    function test_cryptPassword(){
        foreach($this->passes as $method => $hash){
            $info = "testing method $method";
            $this->signal('failinfo',$info);
            $this->assertEqual(auth_cryptPassword('foo'.$method,$method,'abcdefgh'),$hash);
        }
    }

    function test_verifyPassword(){
        foreach($this->passes as $method => $hash){
            $info = "testing method $method";
            $this->signal('failinfo',$info);
            $this->assertTrue(auth_verifyPassword('foo'.$method,$hash));
        }
    }

    function test_verifyPassword_nohash(){
        $this->assertTrue(auth_verifyPassword('foo','$1$$n1rTiFE0nRifwV/43bVon/'));
    }

}

//Setup VIM: ex: et ts=4 :
