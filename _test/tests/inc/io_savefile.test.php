<?php

class io_savefile_test extends DokuWikiTest {

    /*
     * dependency for tests needing zlib extension to pass
     */
    public function test_ext_zlib() {
        if (!extension_loaded('zlib')) {
            $this->markTestSkipped('skipping all zlib tests.  Need zlib extension');
        }
    }

    /*
     * dependency for tests needing zlib extension to pass
     */
    public function test_ext_bz2() {
        if (!extension_loaded('bz2')) {
            $this->markTestSkipped('skipping all bzip2 tests.  Need bz2 extension');
        }
    }

    function _write($file){
        $contents = "The\012Write\012Test\012";
        $this->assertTrue(io_saveFile($file, $contents));
        $this->assertEquals($contents, io_readFile($file));
        $this->assertTrue(io_saveFile($file, $contents, true));
        $this->assertEquals($contents.$contents, io_readFile($file));
    }

    function test_write(){
        $this->_write(TMP_DIR.'/test.txt');
    }

    /**
     * @depends test_ext_zlib
     */
    function test_gzwrite(){
        $this->_write(TMP_DIR.'/test.txt.gz');
    }

    /**
     * @depends test_ext_bz2
     */
    function test_bzwrite(){
        $this->_write(TMP_DIR.'/test.txt.bz2');
    }

}
