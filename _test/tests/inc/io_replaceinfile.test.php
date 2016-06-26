<?php

class io_replaceinfile_test extends DokuWikiTest {

    protected $contents = "The\012Delete\012Delete\012Delete01\012Delete02\012Delete\012DeleteX\012Test\012";

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

        io_saveFile($file, $this->contents);
        // Replace one, no regex
        $this->assertTrue(io_replaceInFile($file, "Delete\012", "Delete00\012", false, 1));
        $this->assertEquals("The\012Delete00\012Delete\012Delete01\012Delete02\012Delete\012DeleteX\012Test\012", io_readFile($file));
        // Replace all, no regex
        $this->assertTrue(io_replaceInFile($file, "Delete\012", "DeleteX\012", false, -1));
        $this->assertEquals("The\012Delete00\012DeleteX\012Delete01\012Delete02\012DeleteX\012DeleteX\012Test\012", io_readFile($file));
        // Replace two, regex and backreference
        $this->assertTrue(io_replaceInFile($file, "#Delete(\\d+)\012#", "\\1\012", true, 2));
        $this->assertEquals("The\01200\012DeleteX\01201\012Delete02\012DeleteX\012DeleteX\012Test\012", io_readFile($file));
        // Delete and insert, no regex
        $this->assertTrue(io_replaceInFile($file, "DeleteX\012", "Replace\012", false, 0));
        $this->assertEquals("The\01200\01201\012Delete02\012Test\012Replace\012", io_readFile($file));
    }

    function test_replace(){
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

    /**
     * Test for a non-regex replacement where $newline contains a backreference like construct - it shouldn't affect the replacement
     */
    function test_edgecase1()
    {
        $file = TMP_DIR . '/test.txt';

        io_saveFile($file, $this->contents);
        $this->assertTrue(io_replaceInFile($file, "Delete\012", "Delete\\00\012", false, -1));
        $this->assertEquals("The\012Delete\\00\012Delete\\00\012Delete01\012Delete02\012Delete\\00\012DeleteX\012Test\012", io_readFile($file), "Edge case: backreference like construct in replacement line");
    }
    /**
     * Test with replace all where replacement line == search line - must not timeout
     *
     * @small
     */
    function test_edgecase2() {
        $file = TMP_DIR.'/test.txt';

        io_saveFile($file, $this->contents);
        $this->assertTrue(io_replaceInFile($file, "Delete\012", "Delete\012", false, -1));
        $this->assertEquals("The\012Delete\012Delete\012Delete01\012Delete02\012Delete\012DeleteX\012Test\012", io_readFile($file), "Edge case: new line the same as old line");
    }

    /**
     *    Test where $oldline exactly matches one line and also matches part of other lines - only the exact match should be replaced
     */
    function test_edgecase3()
    {
        $file = TMP_DIR . '/test.txt';
        $contents = "The\012Delete\01201Delete\01202Delete\012Test\012";

        io_saveFile($file, $contents);
        $this->assertTrue(io_replaceInFile($file, "Delete\012", "Replace\012", false, -1));
        $this->assertEquals("The\012Replace\01201Delete\01202Delete\012Test\012", io_readFile($file), "Edge case: old line is a match for parts of other lines");
    }

    /**
     * Test passing an invalid parameter.
     *
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    function test_badparam()
    {
        /* The empty $oldline parameter should be caught before the file doesn't exist test. */
        $this->assertFalse(io_replaceInFile(TMP_DIR.'/not_existing_file.txt', '', '', false, 0));
    }
}
