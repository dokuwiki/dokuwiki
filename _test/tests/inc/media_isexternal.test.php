<?php

class media_isexternal_test extends DokuWikiTest {


    public function test_external(){
        $this->assertTrue(media_isexternal('http://www.example.com/foo.png'));
        $this->assertTrue(media_isexternal('https://www.example.com/foo.png'));
        $this->assertTrue(media_isexternal('ftp://www.example.com/foo.png'));
        $this->assertTrue(media_isexternal('hTTp://www.example.com/foo.png'));
        $this->assertTrue(media_isexternal('hTTps://www.example.com/foo.png'));
        $this->assertTrue(media_isexternal('Ftp://www.example.com/foo.png'));
    }

    public function test_internal(){
        $this->assertFalse(media_isexternal('wiki:logo.png'));
        $this->assertFalse(media_isexternal('private:logo.png'));
        $this->assertFalse(media_isexternal('ftp:private:logo.png'));

    }

}