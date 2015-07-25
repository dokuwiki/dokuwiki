<?php

class io_readfile_test extends DokuWikiTest {

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

    function test_plain(){
        // since git converts line endings, we can't check in this test file but have to create it ourselves
        $plain = TMP_DIR.'/test.txt';
        file_put_contents($plain, "The\015\012Test\015\012");

        $this->assertEquals("The\012Test\012", io_readFile($plain));
        $this->assertEquals("The\015\012Test\015\012", io_readFile($plain, false));
        $this->assertEquals(false, io_readFile(__DIR__.'/io_readfile/nope.txt'));
    }

    /**
     * @depends test_ext_zlib
     */
    function test_gzfiles(){
        $this->assertEquals("The\012Test\012", io_readFile(__DIR__.'/io_readfile/test.txt.gz'));
        $this->assertEquals("The\015\012Test\015\012", io_readFile(__DIR__.'/io_readfile/test.txt.gz', false));
        $this->assertEquals(false, io_readFile(__DIR__.'/io_readfile/nope.txt.gz'));
        $this->assertEquals(false, io_readFile(__DIR__.'/io_readfile/corrupt.txt.gz'));
    }

    /**
     * @depends test_ext_bz2
     */
    function test_bzfiles(){
        $this->assertEquals("The\012Test\012", io_readFile(__DIR__.'/io_readfile/test.txt.bz2'));
        $this->assertEquals("The\015\012Test\015\012", io_readFile(__DIR__.'/io_readfile/test.txt.bz2', false));
        $this->assertEquals(false, io_readFile(__DIR__.'/io_readfile/nope.txt.bz2'));
        $this->assertEquals(false, io_readFile(__DIR__.'/io_readfile/corrupt.txt.bz2'));
        // internal bzfile function
        $this->assertEquals(array("The\015\012","Test\015\012"), bzfile(__DIR__.'/io_readfile/test.txt.bz2', true));
        $this->assertEquals(array_fill(0, 120, str_repeat('a', 80)."\012"), bzfile(__DIR__.'/io_readfile/large.txt.bz2', true));
        $line = str_repeat('a', 8888)."\012";
        $this->assertEquals(array($line,"\012",$line,"!"), bzfile(__DIR__.'/io_readfile/long.txt.bz2', true));
    }

}
