<?php

/**
 * Tests for the ACL enforcement of media_restore().
 *
 * The restore target is a request parameter independent of the namespace the
 * media manager derived its authorization from, so media_restore() must
 * authorize the target media id itself rather than trusting the passed value.
 */
class media_restore_test extends DokuWikiTest {

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
     * Create a current media file and one old revision of it.
     *
     * @param string $id      media id
     * @param int    $rev     revision timestamp
     * @param string $current content of the current file
     * @param string $old     content of the stored revision
     */
    protected function seedRevision($id, $rev, $current, $old) {
        $cur = mediaFN($id);
        io_makeFileDir($cur);
        file_put_contents($cur, $current);

        $att = mediaFN($id, $rev);
        io_makeFileDir($att);
        file_put_contents($att, $old);
    }

    /**
     * Restoring a revision of a media file in an ACL-denied namespace must be
     * rejected, even when the request is otherwise valid.
     */
    public function test_restore_rejects_denied_namespace() {
        $id = 'public:private:secret.png';
        $rev = 1000000000;
        $this->seedRevision($id, $rev, 'CURRENT', 'OLD');

        $res = media_restore($id, $rev);

        $this->assertFalse($res);
        $this->assertSame('CURRENT', file_get_contents(mediaFN($id)), 'protected file must be untouched');
    }

    /**
     * Restoring a revision in a permitted namespace must overwrite the current
     * file with the stored revision.
     */
    public function test_restore_allows_permitted_namespace() {
        $id = 'public:target.png';
        $rev = 1000000000;
        $this->seedRevision($id, $rev, 'CURRENT', 'OLD');

        $res = media_restore($id, $rev);

        $this->assertSame($id, $res);
        $this->assertSame('OLD', file_get_contents(mediaFN($id)), 'current file must hold the restored revision');
    }
}
