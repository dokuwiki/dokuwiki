<?php

class media_ispublic_test extends DokuWikiTest {


    public function test_external(){
        $this->assertTrue(media_ispublic('http://www.example.com/foo.png'));
        $this->assertTrue(media_ispublic('https://www.example.com/foo.png'));
        $this->assertTrue(media_ispublic('hTTp://www.example.com/foo.png'));
        $this->assertTrue(media_ispublic('hTTps://www.example.com/foo.png'));
    }

    public function test_internal(){
        $this->assertTrue(media_ispublic('wiki:logo.png'));
        $this->assertFalse(media_ispublic('private:logo.png'));
    }

}