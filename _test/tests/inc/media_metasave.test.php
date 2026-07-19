<?php

/**
 * Tests for the ACL enforcement of media_metasave().
 *
 * media_metasave() authorizes the target media id itself.
 */
class media_metasave_test extends DokuWikiTest {

    public function setUp(): void {
        parent::setUp();

        global $conf, $AUTH_ACL, $USERINFO;

        $conf['useacl'] = 1;
        $conf['mediarevisions'] = 1;
        $_SERVER['REMOTE_USER'] = 'john';
        $USERINFO['grps'] = ['user'];

        // upload allowed in public:*, but denied in the child public:private:*
        $AUTH_ACL = [
            '*                  @ALL           0',
            'public:*           @user          8', // AUTH_UPLOAD
            'public:private:*   @user          0', // AUTH_NONE
        ];
    }

    /**
     * Saving metadata for a media file in an ACL-denied namespace must be
     * rejected.
     */
    public function test_metasave_rejects_denied_namespace() {
        $id = 'public:private:secret.jpg';
        $file = mediaFN($id);
        io_makeFileDir($file);
        file_put_contents($file, 'PROTECTED');
        $rev = filemtime($file);

        // a valid token, so the ACL check is the only thing that can reject
        $_REQUEST['sectok'] = getSecurityToken();

        $res = media_metasave($id, ['Title' => 'hacked']);

        // a bypass reaches media_saveOldRevision() before any metadata write,
        // so the absence of a stored revision proves the ACL check blocked it
        $this->assertFileDoesNotExist(mediaFN($id, $rev), 'denied metasave must not create a revision');
        $this->assertSame('PROTECTED', file_get_contents($file), 'protected file must be untouched');
        $this->assertFalse($res);
    }
}
