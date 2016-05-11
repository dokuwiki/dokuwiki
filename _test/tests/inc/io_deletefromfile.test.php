<?php

class io_deletefromfile_test extends DokuWikiTest {

    function test_delete(){
        $file = TMP_DIR.'/test.txt';
        $contents = "The\012Delete\012Delete01\012Delete02\012Delete\012DeleteX\012Test\012";
        io_saveFile($file, $contents);
        $this->assertTrue(io_deleteFromFile($file, "Delete\012"));
        $this->assertEquals("The\012Delete01\012Delete02\012DeleteX\012Test\012", io_readFile($file));
        $this->assertTrue(io_deleteFromFile($file, "#Delete\\d+\012#", true));
        $this->assertEquals("The\012DeleteX\012Test\012", io_readFile($file));
    }

}
