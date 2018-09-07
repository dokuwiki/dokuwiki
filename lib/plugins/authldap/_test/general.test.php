<?php
/**
 * General tests for the authldap plugin
 *
 * @group plugin_authldap
 * @group auth_plugins
 * @group plugins
 * @group bundled_plugins
 */
class authldap_plugin_general_test extends DokuWikiTest {
    protected $auth;

    function setUp() {
        parent::setUp();

        $this->auth = new auth_plugin_authldap();
    }

    /**
     * Precomputed hashes
     *
     * for the password foo$method, using abcdefgh12345678912345678912345678 as salt
     *
     * @return array as array(hash method, $hash)
     */
    public function hashes() {
        $passes = array(
            array('smd5', '{CRYPT}$1$abcdefgh$SYbjm2AEvSoHG7Xapi8so.'),
            array('md5', '{MD5}8fa22d62408e5351553acdd91c6b7003'),
            array('sha1', '{SHA}b456d3b0efd105d613744ffd549514ecafcfc7e1'),
            array('ssha', '{SSHA}QMHG+uC7bHNYKkmoLbNsNI38/dJhYmNk'),
            array('lsmd5', '{SMD5}HGbkPrkWgy9KgcRGWlrsUWFiY2RlZmdo'),
            array('crypt', '{CRYPT}ablvoGr1hvZ5k'),

        );

        if(defined('CRYPT_SHA512') && CRYPT_SHA512 == 1) {
            // Check SHA512 only if available in this PHP
            $passes[] = array(
              'sha512',
              '{CRYPT}$6$abcdefgh12345678$J9.zOcgx0lotwZdcz0uulA3IVQMinZvFZVjA5vapRLVAAqtay23XD4xeeUxQ3B4JvDWYFBIxVWW1tOYlHX13k1'
            );
        }

        return $passes;
    }

    /**
     * @dataProvider hashes
     * @param $method
     * @param $hash
     */
    public function test_ldapCryptPassword($method, $hash) {
        $this->assertEquals(
            $this->auth->ldapCryptPassword('foo' . $method, $method, 'abcdefgh12345678912345678912345678'),
            $hash
        );
        $this->assertNotEquals(
            $this->auth->ldapCryptPassword('bar' . $method, $method, 'abcdefgh12345678912345678912345678'),
            $hash
        );
    }

    public function test_ldapCryptPassword_for_UnsupportedMethod() {
        $method = 'apr1';
        $this->assertEquals(
            $this->auth->ldapCryptPassword('foo' . $method, $method, 'abcdefgh12345678912345678912345678'),
            false
        );
    }
}

