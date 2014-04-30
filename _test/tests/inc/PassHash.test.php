<?php

/**
 * Class PassHash_test
 *
 * most tests are in auth_password.test.php
 */
class PassHash_test extends DokuWikiTest {

    function test_hmac(){
        // known hashes taken from https://code.google.com/p/yii/issues/detail?id=1942
        $this->assertEquals('df08aef118f36b32e29d2f47cda649b6', PassHash::hmac('md5','data','secret'));
        $this->assertEquals('9818e3306ba5ac267b5f2679fe4abd37e6cd7b54', PassHash::hmac('sha1','data','secret'));

        // known hashes from https://en.wikipedia.org/wiki/Hash-based_message_authentication_code
        $this->assertEquals('74e6f7298a9c2d168935f58c001bad88', PassHash::hmac('md5','',''));
        $this->assertEquals('fbdb1d1b18aa6c08324b7d64b71fb76370690e1d', PassHash::hmac('sha1','',''));
        $this->assertEquals('80070713463e7749b90c2dc24911e275', PassHash::hmac('md5','The quick brown fox jumps over the lazy dog','key'));
        $this->assertEquals('de7c9b85b8b78aa6bc8a7a36f70a90701c9db4d9', PassHash::hmac('sha1','The quick brown fox jumps over the lazy dog','key'));

    }
}