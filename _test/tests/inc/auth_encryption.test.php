<?php

/**
 * Tests the auth_decrypt and auth_encrypt-functions
 */
class auth_encryption_test extends DokuWikiTest
{
    function testDeEncrypt()
    {
        $data = "OnA28asdfäakgß*+!\"+*";
        $secret = "oeaf1öasdöflk§";
        $this->assertEquals($data, auth_decrypt(auth_encrypt($data, $secret), $secret));
    }

    /**
     * Try to decode a known secret. This one has been created with phpseclib Version 2
     */
    function testCompatibility()
    {
        $secret = 'secret';
        $plain = 'This is secret';
        $crypt = '837e9943623a34fe340e89024c28f4e9be13bbcacdd139801ef16a27bffa7714';
        $this->assertEquals($plain, auth_decrypt(hex2bin($crypt), $secret));
    }
}
