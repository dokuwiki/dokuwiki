<?php
/**
 * Test for blowfish encryption.
 */
class blowfish_test extends DokuWikiTest {
    public function testEncryptDecryptNumbers() {
        $secret = '$%ÄüfuDFRR';
        $string = '12345678';
        $this->assertEquals(
            $string,
            PMA_blowfish_decrypt(PMA_blowfish_encrypt($string, $secret), $secret)
        );
    }

    public function testEncryptDecryptChars() {
        $secret = '$%ÄüfuDFRR';
        $string = 'abcDEF012!"§$%&/()=?`´"\',.;:-_#+*~öäüÖÄÜ^°²³';
        $this->assertEquals(
            $string,
            PMA_blowfish_decrypt(PMA_blowfish_encrypt($string, $secret), $secret)
        );
    }

    // FS#1690 FS#1713
    public function testEncryptDecryptBinary() {
        $secret = '$%ÄüfuDFRR';
        $string = "this is\0binary because of\0zero bytes";
        $this->assertEquals(
            $string,
            PMA_blowfish_decrypt(PMA_blowfish_encrypt($string, $secret), $secret)
        );
    }
}
