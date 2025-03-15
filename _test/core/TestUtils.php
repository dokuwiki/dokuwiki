<?php

/**
 * Helper class with some filesystem utilities.
 */
class TestUtils {

    /**
     * converts path to unix-like on windows OS
     * @param string $path UNIX-like path to be converted
     * @return string
     */
    public static function w2u($path) {
        return isWindows() ? str_replace('\\', '/', $path) : $path;
    }

    /**
     * helper for recursive copy()
     *
     * @static
     * @param $destdir string
     * @param $source string
     */
    public static function rcopy($destdir, $source) {
        if (!is_dir($source)) {
            copy($source, $destdir.'/'.basename($source));
        } else {
            $newdestdir = $destdir.'/'.basename($source);
            if (!is_dir($newdestdir)) {
                mkdir($newdestdir);
            }

            $dh = dir($source);
            while (false !== ($entry = $dh->read())) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                TestUtils::rcopy($newdestdir, $source.'/'.$entry);
            }
            $dh->close();
        }

        system('sync'); // temporary attempt to fix tests
    }

    /**
     * helper for recursive rmdir()/unlink()
     *
     * @static
     * @param $target string
     */
    public static function rdelete($target) {
        if (!is_dir($target)) {
            unlink($target);
        } else {
            $dh = dir($target);
            while (false !== ($entry = $dh->read())) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                TestUtils::rdelete("$target/$entry");
            }
            $dh->close();
            rmdir($target);
        }
    }

    /**
     * helper to append text to a file
     *
     * @static
     * @param $file string
     * @param $text string
     */
    public static function fappend($file, $text) {
        $fh = fopen($file, 'a');
        fwrite($fh, $text);
        fclose($fh);
    }

}
