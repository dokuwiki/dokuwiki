<?php

class io_getSizeFile_test extends DokuWikiTest {

    /*
     * dependency for tests needing zlib extension to pass
     */
    public function test_ext_zlib() {
        if (!DOKU_HAS_GZIP) {
            $this->markTestSkipped('skipping all zlib tests.  Need zlib extension');
        }
        $this->assertTrue(true); // avoid being marked as risky for having no assertion
    }

    /*
     * dependency for tests needing zlib extension to pass
     */
    public function test_ext_bz2() {
        if (!DOKU_HAS_BZIP) {
            $this->markTestSkipped('skipping all bzip2 tests.  Need bz2 extension');
        }
        $this->assertTrue(true); // avoid being marked as risky for having no assertion
    }

    function test_plain(){
        // since git converts line endings, we can't check in this test file but have to create it ourselves
        $plain = TMP_DIR.'/test.txt';
        file_put_contents($plain, "The\015\012Test\015\012");

        $this->assertEquals(11, io_getSizeFile($plain));
        $this->assertEquals(0, io_getSizeFile(__DIR__.'/io_readfile/nope.txt'));
        $plain_mb = TMP_DIR.'/test.txt';
        io_saveFile($plain_mb, "string with utf-8 chars åèö - doo-bee doo-bee dooh\012");
        $this->assertEquals(54, io_getSizeFile($plain_mb));
    }

    /**
     * @depends test_ext_zlib
     */
    function test_gzfiles(){
        $this->assertEquals(11, io_getSizeFile(__DIR__.'/io_readfile/test.txt.gz'));
        $this->assertEquals(0, io_getSizeFile(__DIR__.'/io_readfile/nope.txt.gz'));
        $this->assertEquals(11, io_getSizeFile(__DIR__.'/io_readfile/corrupt.txt.gz'));
        $gz_mb = TMP_DIR.'/test.txt.gz';
        io_saveFile($gz_mb, "string with utf-8 chars åèö - doo-bee doo-bee dooh\012");
        $this->assertEquals(54, io_getSizeFile($gz_mb));
    }

    /**
     * @depends test_ext_bz2
     */
    function test_bzfiles(){

        $this->assertEquals(11, io_getSizeFile(__DIR__.'/io_readfile/test.txt.bz2'));
        $this->assertEquals(0, io_getSizeFile(__DIR__.'/io_readfile/nope.txt.bz2'));
        $this->assertEquals(0, io_getSizeFile(__DIR__.'/io_readfile/corrupt.txt.bz2'));
        $this->assertEquals(9720, io_getSizeFile(__DIR__.'/io_readfile/large.txt.bz2'));
        $this->assertEquals(17780, io_getSizeFile(__DIR__.'/io_readfile/long.txt.bz2'));
        $bz_mb = TMP_DIR.'/test.txt.bz2';
        io_saveFile($bz_mb, "string with utf-8 chars åèö - doo-bee doo-bee dooh\012");
        $this->assertEquals(54, io_getSizeFile($bz_mb));
    }

}
