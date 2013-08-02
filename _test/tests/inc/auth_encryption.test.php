<?php

/**
 * Tests the auth_decrypt and auth_encrypt-functions
 */
class auth_encryption_test extends DokuWikiTest {
    function testDeEncrypt() {
        $data = "OnA28asdfäakgß*+!\"+*";
        $secret = "oeaf1öasdöflk§";
        $this->assertEquals($data, auth_decrypt(auth_encrypt($data, $secret), $secret));
    }
}
