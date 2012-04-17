<?php

class TestUtils {

    /**
     * helper for recursive copy()
     */
    static function rcopy($destdir, $source) {
        if (!is_dir($source)) {
            copy($source, $destdir.'/'.basename($source));
        } else {
            $newdestdir = $destdir.'/'.basename($source);
            mkdir($newdestdir);

            $dh = dir($source);
            while (false !== ($entry = $dh->read())) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                TestUtils::rcopy($newdestdir, $source.'/'.$entry);
            }
            $dh->close();
        }
    }

    /**
     * helper for recursive rmdir()/unlink()
     */
    static function rdelete($target) {
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

    // helper to append text to a file
    static function fappend($file, $text) {
        $fh = fopen($file, 'a');
        fwrite($fh, $text);
        fclose($fh);
    }

}
