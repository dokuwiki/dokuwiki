<?php

class auth_password_test extends DokuWikiTest {

    /**
     *  precomputed hashes
     *
     * for the password foo$method, using abcdefgh12345678912345678912345678 as salt
     *
     * @return array
     */
    public function hashes() {

        $passes = array(
            array('smd5', '$1$abcdefgh$SYbjm2AEvSoHG7Xapi8so.'),
            array('apr1', '$apr1$abcdefgh$C/GzYTF4kOVByYLEoD5X4.'),
            array('md5', '8fa22d62408e5351553acdd91c6b7003'),
            array('sha1', 'b456d3b0efd105d613744ffd549514ecafcfc7e1'),
            array('ssha', '{SSHA}QMHG+uC7bHNYKkmoLbNsNI38/dJhYmNk'),
            array('lsmd5', '{SMD5}HGbkPrkWgy9KgcRGWlrsUWFiY2RlZmdo'),
            array('crypt', 'ablvoGr1hvZ5k'),
            array('mysql', '4a1fa3780bd6fd55'),
            array('my411', '*E5929347E25F82E19E4EBE92F1DC6B6E7C2DBD29'),
            array('kmd5', 'a579299436d7969791189acadd86fcb716'),
            array('djangomd5', 'md5$abcde$d0fdddeda8cd92725d2b54148ac09158'),
            array('djangosha1', 'sha1$abcde$c8e65a7f0acc9158843048a53dcc5a6bc4d17678'),

        );

        if(defined('CRYPT_SHA512') && CRYPT_SHA512 == 1) {
            // Check SHA512 only if available in this PHP
            $passes[] = array('sha512', '$6$abcdefgh12345678$J9.zOcgx0lotwZdcz0uulA3IVQMinZvFZVjA5vapRLVAAqtay23XD4xeeUxQ3B4JvDWYFBIxVWW1tOYlHX13k1');
        }
        if(function_exists('hash_pbkdf2')) {
            if(in_array('sha256', hash_algos())) {
                $passes[] = array('djangopbkdf2_sha256', 'pbkdf2_sha256$24000$abcdefgh1234$R23OyZJ0nGHLG6MvPNfEkV5AOz3jUY5zthByPXs2gn0=');
            }
            if(in_array('sha1', hash_algos())) {
                $passes[] = array('djangopbkdf2_sha1', 'pbkdf2_sha1$24000$abcdefgh1234$pOliX4vV1hgOv7lFNURIHHx41HI=');
            }
        }
        return $passes;
    }

    /**
     * @dataProvider hashes
     * @param $method
     * @param $hash
     */
    function test_cryptPassword($method, $hash) {
        $this->assertEquals(
            $hash,
            auth_cryptPassword('foo' . $method, $method, 'abcdefgh12345678912345678912345678')
        );
    }

    /**
     * @dataProvider hashes
     * @param $method
     * @param $hash
     */
    function test_verifyPassword($method, $hash) {
        $this->assertTrue(auth_verifyPassword('foo' . $method, $hash));
        $this->assertFalse(auth_verifyPassword('bar' . $method, $hash));
    }

    /**
     * @dataProvider hashes
     * @param $method
     * @param $hash
     */
    function test_verifySelf($method, $hash) {
        $hash = auth_cryptPassword('foo' . $method, $method);
        $this->assertTrue(auth_verifyPassword('foo' . $method, $hash));
    }

    function test_bcrypt_self() {
        $hash = auth_cryptPassword('foobcrypt', 'bcrypt');
        $this->assertTrue(auth_verifyPassword('foobcrypt', $hash));
    }

    function test_verifyPassword_fixedbcrypt() {
        $this->assertTrue(auth_verifyPassword('foobcrypt', '$2a$12$uTWercxbq4sjp2xAzv3we.ZOxk51m5V/Bv5bp2H27oVFJl5neFQoC'));
    }

    function test_verifyPassword_nohash() {
        $this->assertTrue(auth_verifyPassword('foo', '$1$$n1rTiFE0nRifwV/43bVon/'));
    }

    function test_verifyPassword_fixedpmd5() {
        $this->assertTrue(auth_verifyPassword('test12345', '$P$9IQRaTwmfeRo7ud9Fh4E2PdI0S3r.L0'));
        $this->assertTrue(auth_verifyPassword('test12345', '$H$9IQRaTwmfeRo7ud9Fh4E2PdI0S3r.L0'));
    }

    function test_veryPassword_mediawiki() {
        $this->assertTrue(auth_verifyPassword('password', ':B:838c83e1:e4ab7024509eef084cdabd03d8b2972c'));
    }

    /**
     * pmd5 checking should throw an exception when a hash with a too high
     * iteration count is passed
     */
    function test_verifyPassword_pmd5Exception() {
        $except = false;
        try {
            auth_verifyPassword('foopmd5', '$H$abcdefgh1ZbJodHxmeXVAhEzTG7IAp.');
        } catch(Exception $e) {
            $except = true;
        }
        $this->assertTrue($except);
    }

    /**
     * issue #2629, support PHP's crypt() format (with rounds=0 parameter)
     */
    function test_verifyPassword_sha512_crypt() {
        if(defined('CRYPT_SHA512') && CRYPT_SHA512 == 1) {
            $this->assertTrue(auth_verifyPassword('Qwerty123', '$6$rounds=3000$9in6UciYPFG6ydsJ$YBjypQ7XoRqvJoX1a2.spSysSVHcdreVXi1Xh5SyOxo2yNSxDjlUCun2YXrwk9.YP6vmRvCWrhp0fbPgSOT7..'));
        } else {
            $this->markTestSkipped('SHA512 not available in this PHP environment');
        }
    }

}
