<?php

class init_fullpath_test extends DokuWikiTest {

    function test_unix_paths(){
        $base = $_SERVER['SCRIPT_FILENAME'];
        $_SERVER['SCRIPT_FILENAME'] = '/absolute/path/self.php';
        $GLOBALS['DOKU_UNITTEST_ASSUME_WINDOWS'] = false;

        // paths to check
        $tests = array(
                        '/foo/bar/baz' => '/foo/bar/baz',
                        '/foo//bar/baz' => '/foo/bar/baz',
                        '/foo/../bar/baz' => '/bar/baz',
                        '/foo/./bar/baz' => '/foo/bar/baz',
                        '/foo/bar/..' => '/foo',
                        '/foo/bar/../../../baz' => '/baz',

                        'foo/bar/baz' => '/absolute/path/foo/bar/baz',
                        'foo//bar/baz' => '/absolute/path/foo/bar/baz',
                        'foo/../bar/baz' => '/absolute/path/bar/baz',
                        'foo/./bar/baz' => '/absolute/path/foo/bar/baz',
                        'foo/bar/..' => '/absolute/path/foo',
                        'foo/bar/../../../baz' => '/absolute/baz',
                      );

        foreach($tests as $from => $to){
            $info = "Testing '$from' resulted in '".fullpath($from)."'";

            $this->assertEquals(fullpath($from), $to, $info);
        }


        $_SERVER['SCRIPT_FILENAME'] = $base;
        unset($GLOBALS['DOKU_UNITTEST_ASSUME_WINDOWS']);
    }

    function test_windows_paths(){
        $base = $_SERVER['SCRIPT_FILENAME'];
        $_SERVER['SCRIPT_FILENAME'] = '/absolute/path/self.php';
        $GLOBALS['DOKU_UNITTEST_ASSUME_WINDOWS'] = true;

        // paths to check
        $tests = array(
                        'c:foo/bar/baz' => 'c:/foo/bar/baz',
                        'c:foo//bar/baz' => 'c:/foo/bar/baz',
                        'c:foo/../bar/baz' => 'c:/bar/baz',
                        'c:foo/./bar/baz' => 'c:/foo/bar/baz',
                        'c:foo/bar/..' => 'c:/foo',
                        'c:foo/bar/../../../baz' => 'c:/baz',

                        'c:/foo/bar/baz' => 'c:/foo/bar/baz',
                        'c:/foo//bar/baz' => 'c:/foo/bar/baz',
                        'c:/foo/../bar/baz' => 'c:/bar/baz',
                        'c:/foo/./bar/baz' => 'c:/foo/bar/baz',
                        'c:/foo/bar/..' => 'c:/foo',
                        'c:/foo/bar/../../../baz' => 'c:/baz',

                        'c:\\foo\\bar\\baz' => 'c:/foo/bar/baz',
                        'c:\\foo\\\\bar\\baz' => 'c:/foo/bar/baz',
                        'c:\\foo\\..\\bar\\baz' => 'c:/bar/baz',
                        'c:\\foo\\.\\bar\\baz' => 'c:/foo/bar/baz',
                        'c:\\foo\\bar\\..' => 'c:/foo',
                        'c:\\foo\\bar\\..\\..\\..\\baz' => 'c:/baz',

                        '\\\\server\\share/foo/bar/baz' => '\\\\server\\share/foo/bar/baz',
                        '\\\\server\\share/foo//bar/baz' => '\\\\server\\share/foo/bar/baz',
                        '\\\\server\\share/foo/../bar/baz' => '\\\\server\\share/bar/baz',
                        '\\\\server\\share/foo/./bar/baz' => '\\\\server\\share/foo/bar/baz',
                        '\\\\server\\share/foo/bar/..' => '\\\\server\\share/foo',
                        '\\\\server\\share/foo/bar/../../../baz' => '\\\\server\\share/baz',
                      );

        foreach($tests as $from => $to){
            $info = "Testing '$from' resulted in '".fullpath($from)."'";

            $this->assertEquals(fullpath($from), $to, $info);
        }


        $_SERVER['SCRIPT_FILENAME'] = $base;
        unset($GLOBALS['DOKU_UNITTEST_ASSUME_WINDOWS']);
    }

}
//Setup VIM: ex: et ts=4 :
