<?php

class MediaFile_test extends DokuWikiTest {

    public function test_external() {
        $this->assertTrue((new MediaFile('http://www.example.com/foo.png'))->isExternal());
        $this->assertTrue((new MediaFile('https://www.example.com/foo.png'))->isExternal());
        $this->assertTrue((new MediaFile('hTTp://www.example.com/foo.png'))->isExternal());
        $this->assertTrue((new MediaFile('hTTps://www.example.com/foo.png'))->isExternal());
        $this->assertFalse((new MediaFile('wiki:logo.png'))->isExternal());
        $this->assertFalse((new MediaFile('private:logo.png'))->isExternal());
    }

    public function test_exists() {
        $ml = new MediaFile('foo');
        $this->assertFalse($ml->exists(), 'foo');

        $ml = new MediaFile('wiki:dokuwiki-128.png');
        $this->assertTrue($ml->exists(), 'logo');

        $ml = new MediaFile('https://www.google.de/images/srpr/logo4w.png');
        $this->assertFalse($ml->exists(), 'google');
    }

    public function test_ispublic() {
        $this->assertTrue((new MediaFile('https://www.google.de/images/srpr/logo4w.png'))->isPublic());

        $this->assertTrue((new MediaFile('http://www.example.com/foo.png'))->isPublic());
        $this->assertTrue((new MediaFile('https://www.example.com/foo.png'))->isPublic());
        $this->assertTrue((new MediaFile('hTTp://www.example.com/foo.png'))->isPublic());
        $this->assertTrue((new MediaFile('hTTps://www.example.com/foo.png'))->isPublic());

        $this->assertTrue((new MediaFile('wiki:logo.png'))->isPublic());
        $this->assertFalse((new MediaFile('private:logo.png'))->isPublic());
    }

    public function test_upload() {
        $ml = new MediaFile('new1.png');

        // upload
        $ml->uploadFile(mediaFN('wiki:dokuwiki-128.png'), false);
        $this->assertTrue($ml->exists());
        $this->assertFalse($ml->isExternal());
        $this->assertEquals(mediaFN('new1.png'), $ml->getFile());
    }

    /**
     * @expectedException     MediaPermissionException
     * @expectedExceptionCode 1
     */
    public function test_upload_delete_fail() {
        $ml = new MediaFile('new2.png');

        // upload
        $ml->uploadFile(mediaFN('wiki:dokuwiki-128.png'), false);
        // delete should throw ACL exception
        $ml->delete();
    }

    public function test_upload_delete_success() {
        $ml = new MediaFile('full:new2.png');

        // upload
        $ml->uploadFile(mediaFN('wiki:dokuwiki-128.png'), false);
        // delete should work
        $ml->delete();
        $this->assertFalse($ml->exists(), 'existing');
    }

    /**
     * @expectedException     MediaPermissionException
     * @expectedExceptionCode 3
     */
    public function test_overwritecheck() {
        $ml = new MediaFile('wiki:dokuwiki-128.png');
        $ml->uploadFile(mediaFN('wiki:dokuwiki-128.png'), false);
    }

    /**
     * @expectedException     MediaPermissionException
     * @expectedExceptionCode 1
     */
    public function test_uploadpermission() {
        $ml = new MediaFile('private:dokuwiki-128.png');
        $ml->uploadFile(mediaFN('wiki:dokuwiki-128.png'), false);
    }

}