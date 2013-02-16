<?php

class media_get_from_url_test extends DokuWikiTest {

    /**
     * @group internet
     */
    public function test_cache(){
        global $conf;
        $conf['fetchsize'] = 500*1024; //500kb


        $local = media_get_from_URL('http://www.google.com/images/srpr/logo3w.png','png',-1);
        $this->assertTrue($local !== false);
        $this->assertFileExists($local);

        // remember time stamp
        $time = filemtime($local);
        clearstatcache(false, $local);
        sleep(1);

        // fetch again and make sure we got a cache file
        $local = media_get_from_URL('http://www.google.com/images/srpr/logo3w.png','png',-1);
        clearstatcache(false, $local);
        $this->assertTrue($local !== false);
        $this->assertFileExists($local);
        $this->assertEquals($time, filemtime($local));

        unlink($local);
    }

    /**
     * @group internet
     */
    public function test_nocache(){
        global $conf;
        $conf['fetchsize'] = 500*1024; //500kb

        $local = media_get_from_URL('http://www.google.com/images/srpr/logo3w.png','png',0);
        $this->assertFalse($local);
    }

    /**
     * @group internet
     * @group slow
     */
    public function test_recache(){
        global $conf;
        $conf['fetchsize'] = 500*1024; //500kb


        $local = media_get_from_URL('http://www.google.com/images/srpr/logo3w.png','png',5);
        $this->assertTrue($local !== false);
        $this->assertFileExists($local);

        // remember time stamp
        $time = filemtime($local);
        clearstatcache(false, $local);
        sleep(1);

        // fetch again and make sure we got a cache file
        $local = media_get_from_URL('http://www.google.com/images/srpr/logo3w.png','png',5);
        clearstatcache(false, $local);
        $this->assertTrue($local !== false);
        $this->assertFileExists($local);
        $this->assertEquals($time, filemtime($local));

        clearstatcache(false, $local);
        sleep(6);

        // fetch again and make sure we got a new file
        $local = media_get_from_URL('http://www.google.com/images/srpr/logo3w.png','png',5);
        clearstatcache(false, $local);
        $this->assertTrue($local !== false);
        $this->assertFileExists($local);
        $this->assertNotEquals($time, filemtime($local));

        unlink($local);
    }
}