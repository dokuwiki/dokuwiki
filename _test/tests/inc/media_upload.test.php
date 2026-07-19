<?php

/**
 * Tests for the ACL enforcement around media uploads.
 *
 * media_save() trusts the authorization level supplied by its caller so that
 * delegated saves (for example the bureaucracy plugin's "runas" uploads) keep
 * working. The public upload endpoints media_upload() and media_upload_xhr()
 * must therefore authorize the resolved target id themselves, because the
 * requested media id may contain namespace separators pointing into a
 * namespace other than the one the upload form was opened for.
 */
class media_upload_test extends DokuWikiTest {

    public function setUp(): void {
        parent::setUp();

        global $conf, $AUTH_ACL, $USERINFO, $MSG;

        $conf['useacl'] = 1;
        $_SERVER['REMOTE_USER'] = 'john';
        $USERINFO['grps'] = ['user'];
        $MSG = [];

        // upload allowed in public:* and public:sub:*, but denied in the
        // sibling child namespace public:private:*
        $AUTH_ACL = [
            '*                  @ALL           0',
            'public:*           @user          8', // AUTH_UPLOAD
            'public:sub:*       @user          8', // AUTH_UPLOAD
            'public:private:*   @user          0', // AUTH_NONE
        ];
    }

    /**
     * Provide a fresh temporary copy of a valid PNG.
     *
     * @return string path to the temporary file
     */
    protected function tmpImage() {
        global $conf;
        $orig = mediaFN('wiki:dokuwiki-128.png');
        $tmp = $conf['tmpdir'] . '/media_upload_test_' . md5($orig . microtime()) . '.png';
        copy($orig, $tmp);
        return $tmp;
    }

    /**
     * Simulate a media_upload() request for the given namespace and media id.
     *
     * @param string $ns      the namespace the upload form was opened for
     * @param string $mediaid the requested (possibly nested) media id
     * @return false|string the media_upload() return value
     */
    protected function upload($ns, $mediaid) {
        global $MSG;
        $MSG = [];

        $_POST['mediaid'] = $mediaid;
        $_POST['ow'] = '';
        $_REQUEST['sectok'] = getSecurityToken();

        $file = [
            'name' => noNS($mediaid),
            'tmp_name' => $this->tmpImage(),
            'error' => 0,
            'size' => 100,
        ];

        return media_upload($ns, AUTH_UPLOAD, $file);
    }

    /**
     * Collect the messages emitted during the last request.
     *
     * @return string[]
     */
    protected function messages() {
        global $MSG;
        return array_map(static fn($m) => $m['msg'], $MSG ?? []);
    }

    /**
     * A nested media id must be authorized against its own namespace, so an
     * upload into an ACL-denied child namespace is rejected even though the
     * form was opened for a permitted parent namespace.
     */
    public function test_upload_rejects_denied_child_namespace() {
        $res = $this->upload('public', 'private:target.png');

        $this->assertFalse($res);
        $this->assertContains(
            "You don't have permissions to upload files.",
            $this->messages(),
            'the upload must be denied by the ACL check'
        );
        $this->assertFileDoesNotExist(mediaFN('public:private:target.png'));
    }

    /**
     * A nested media id into a permitted namespace must pass the ACL check.
     *
     * The actual move fails in the test environment (is_uploaded_file()), so we
     * only assert that the request got past authorization.
     */
    public function test_upload_allows_permitted_child_namespace() {
        $res = $this->upload('public', 'sub:target.png');

        $this->assertFalse($res);
        $this->assertNotContains(
            "You don't have permissions to upload files.",
            $this->messages(),
            'the upload must pass the ACL check for a permitted target'
        );
    }

    /**
     * media_save() must trust the authorization level supplied by the caller,
     * so a privileged (delegated) save succeeds even when the current session
     * user has no access to the target namespace.
     */
    public function test_save_trusts_supplied_auth() {
        $id = 'public:private:delegated.png';
        $res = media_save(
            ['name' => $this->tmpImage(), 'mime' => 'image/png', 'ext' => 'png'],
            $id,
            false,
            AUTH_UPLOAD,
            'copy'
        );

        $this->assertSame($id, $res);
        $this->assertFileExists(mediaFN($id));
    }

    /**
     * media_save() must still reject a caller that supplies an insufficient
     * authorization level.
     */
    public function test_save_enforces_supplied_auth() {
        $id = 'public:target.png';
        $res = media_save(
            ['name' => $this->tmpImage(), 'mime' => 'image/png', 'ext' => 'png'],
            $id,
            false,
            AUTH_READ,
            'copy'
        );

        $this->assertIsArray($res);
        $this->assertSame(-1, $res[1]);
        $this->assertFileDoesNotExist(mediaFN($id));
    }
}
