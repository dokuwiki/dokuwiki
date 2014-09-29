<?php

class io_rmdir_test extends DokuWikiTest {

    function test_nopes(){
        // set up test dir
        $dir = realpath(io_mktmpdir());
        $top = dirname($dir);
        $this->assertTrue($dir !== false);
        $this->assertTrue(is_dir($dir));

        // switch into it
        $this->assertTrue(chdir($dir));
        $this->assertEquals($dir, getcwd());


        $this->assertFalse(io_rmdir('', false));
        clearstatcache();
        $this->assertTrue(is_dir($dir));
        $this->assertTrue(is_dir($top));

        $this->assertFalse(io_rmdir('', true));
        clearstatcache();
        $this->assertTrue(is_dir($dir));
        $this->assertTrue(is_dir($top));

        $this->assertFalse(io_rmdir(null, false));
        clearstatcache();
        $this->assertTrue(is_dir($dir));
        $this->assertTrue(is_dir($top));

        $this->assertFalse(io_rmdir(null, true));
        clearstatcache();
        $this->assertTrue(is_dir($dir));
        $this->assertTrue(is_dir($top));

        $this->assertFalse(io_rmdir(false, false));
        clearstatcache();
        $this->assertTrue(is_dir($dir));
        $this->assertTrue(is_dir($top));

        $this->assertFalse(io_rmdir(false, true));
        clearstatcache();
        $this->assertTrue(is_dir($dir));
        $this->assertTrue(is_dir($top));

        $this->assertFalse(io_rmdir(array(), false));
        clearstatcache();
        $this->assertTrue(is_dir($dir));
        $this->assertTrue(is_dir($top));

        $this->assertFalse(io_rmdir(array(), true));
        clearstatcache();
        $this->assertTrue(is_dir($dir));
        $this->assertTrue(is_dir($top));

        $this->assertFileNotExists("$dir/this/does/not/exist");
        $this->assertTrue(io_rmdir("$dir/this/does/not/exist"));
        clearstatcache();
        $this->assertFileNotExists("$dir/this/does/not/exist");
        $this->assertTrue(is_dir($dir));
        $this->assertTrue(is_dir($top));
    }


    function test_empty_single(){
        // set up test dir
        $dir = io_mktmpdir();
        $top = dirname($dir);
        $this->assertTrue($dir !== false);
        $this->assertTrue(is_dir($dir));

        // delete successfully
        $this->assertTrue(io_rmdir($dir, false));

        // check result
        clearstatcache();
        $this->assertFalse(is_dir($dir));
        $this->assertTrue(is_dir($top));

        // same again with deletefiles

        // set up test dir
        $dir = io_mktmpdir();
        $this->assertTrue($dir !== false);
        $this->assertTrue(is_dir($dir));

        // delete successfully
        $this->assertTrue(io_rmdir($dir, true));

        // check result
        clearstatcache();
        $this->assertFalse(is_dir($dir));
        $this->assertTrue(is_dir($top));
    }


    function test_empty_hierarchy(){
        // setup hierachy and test it exists
        $dir = io_mktmpdir();
        $top = dirname($dir);
        $this->assertTrue($dir !== false);
        $this->assertTrue(is_dir($dir));
        $this->assertTrue(io_mkdir_p("$dir/foo/bar/baz"));
        $this->assertTrue(is_dir("$dir/foo/bar/baz"));
        $this->assertTrue(io_mkdir_p("$dir/foobar/bar/baz"));
        $this->assertTrue(is_dir("$dir/foobar/bar/baz"));

        // delete successfully
        $this->assertTrue(io_rmdir($dir, false));

        // check result
        clearstatcache();
        $this->assertFalse(is_dir("$dir/foo/bar/baz"));
        $this->assertFalse(is_dir("$dir/foobar/bar/baz"));
        $this->assertFalse(is_dir($dir));
        $this->assertTrue(is_dir($top));

        // same again with deletefiles

        // setup hierachy and test it exists
        $dir = io_mktmpdir();
        $this->assertTrue($dir !== false);
        $this->assertTrue(is_dir($dir));
        $this->assertTrue(io_mkdir_p("$dir/foo/bar/baz"));
        $this->assertTrue(is_dir("$dir/foo/bar/baz"));
        $this->assertTrue(io_mkdir_p("$dir/foobar/bar/baz"));
        $this->assertTrue(is_dir("$dir/foobar/bar/baz"));

        // delete successfully
        $this->assertTrue(io_rmdir($dir, true));

        // check result
        clearstatcache();
        $this->assertFalse(is_dir("$dir/foo/bar/baz"));
        $this->assertFalse(is_dir("$dir/foobar/bar/baz"));
        $this->assertFalse(is_dir($dir));
        $this->assertTrue(is_dir($top));
    }

    function test_full_single(){
        // set up test dir
        $dir = io_mktmpdir();
        $top = dirname($dir);
        $this->assertTrue($dir !== false);
        $this->assertTrue(is_dir($dir));

        // put file
        $this->assertTrue(io_saveFile("$dir/testfile.txt", 'foobar'));
        $this->assertFileExists("$dir/testfile.txt");

        // delete unsuccessfully
        $this->assertFalse(io_rmdir($dir, false));

        // check result
        clearstatcache();
        $this->assertFileExists("$dir/testfile.txt");
        $this->assertTrue(is_dir($dir));
        $this->assertTrue(is_dir($top));

        // same again with deletefiles

        // delete successfully
        $this->assertTrue(io_rmdir($dir, true));

        // check result
        clearstatcache();
        $this->assertFileNotExists("$dir/testfile.txt");
        $this->assertFalse(is_dir($dir));
        $this->assertTrue(is_dir($top));
    }

    function test_full_hierarchy(){
        // setup hierachy and test it exists
        $dir = io_mktmpdir();
        $top = dirname($dir);
        $this->assertTrue($dir !== false);
        $this->assertTrue(is_dir($dir));
        $this->assertTrue(io_mkdir_p("$dir/foo/bar/baz"));
        $this->assertTrue(is_dir("$dir/foo/bar/baz"));
        $this->assertTrue(io_mkdir_p("$dir/foobar/bar/baz"));
        $this->assertTrue(is_dir("$dir/foobar/bar/baz"));

        // put files
        $this->assertTrue(io_saveFile("$dir/testfile.txt", 'foobar'));
        $this->assertFileExists("$dir/testfile.txt");
        $this->assertTrue(io_saveFile("$dir/foo/testfile.txt", 'foobar'));
        $this->assertFileExists("$dir/foo/testfile.txt");
        $this->assertTrue(io_saveFile("$dir/foo/bar/baz/testfile.txt", 'foobar'));
        $this->assertFileExists("$dir/foo/bar/baz/testfile.txt");

        // delete unsuccessfully
        $this->assertFalse(io_rmdir($dir, false));

        // check result
        clearstatcache();
        $this->assertFileExists("$dir/testfile.txt");
        $this->assertFileExists("$dir/foo/testfile.txt");
        $this->assertFileExists("$dir/foo/bar/baz/testfile.txt");
        $this->assertTrue(is_dir("$dir/foo/bar/baz"));
        $this->assertTrue(is_dir("$dir/foobar/bar/baz"));
        $this->assertTrue(is_dir($dir));
        $this->assertTrue(is_dir($top));

        // delete successfully
        $this->assertTrue(io_rmdir($dir, true));

        // check result
        clearstatcache();
        $this->assertFileNotExists("$dir/testfile.txt");
        $this->assertFileNotExists("$dir/foo/testfile.txt");
        $this->assertFileNotExists("$dir/foo/bar/baz/testfile.txt");
        $this->assertFalse(is_dir("$dir/foo/bar/baz"));
        $this->assertFalse(is_dir("$dir/foobar/bar/baz"));
        $this->assertFalse(is_dir($dir));
        $this->assertTrue(is_dir($top));
    }

}