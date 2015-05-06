<?php

class io_deletefromfile_test extends DokuWikiTest {

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
        $contents = "The\012Delete\012Delete01\012Delete02\012Delete\012DeleteX\012Test\012";
        io_saveFile($file, $contents);
        $this->assertTrue(io_deleteFromFile($file, "Delete\012"));
        $this->assertEquals("The\012Delete01\012Delete02\012DeleteX\012Test\012", io_readFile($file));
        $this->assertTrue(io_deleteFromFile($file, "#Delete\\d+\012#", true));
        $this->assertEquals("The\012DeleteX\012Test\012", io_readFile($file));
    }

    function test_delete(){
        $this->_write(TMP_DIR.'/test.txt');
    }

//    /**
//     * @depends test_ext_zlib
//     */
//    function test_gzwrite(){
//    }

}
