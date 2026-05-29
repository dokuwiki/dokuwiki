<?php

use dokuwiki\test\mock\AuthPlugin;

/**
 * Tests for mediaAclPath() and its effect on media ACL evaluation.
 */
class auth_mediaaclpath_test extends DokuWikiTest
{
    public function setUp(): void
    {
        parent::setUp();
        global $auth;
        $auth = new AuthPlugin();
    }

    public function provideMediaIds(): array
    {
        return [
            // [media id, expected ACL path]
            'nested namespace'   => ['wiki:sub:image.png', 'wiki:sub:*'],
            'single namespace'   => ['wiki:image.png', 'wiki:*'],
            'root namespace'     => ['image.png', '*'],
            'empty id'           => ['', '*'],
            'page-like id'       => ['wiki:secret.png', 'wiki:*'],
        ];
    }

    /**
     * @dataProvider provideMediaIds
     */
    public function test_mediaAclPath_transform($id, $expected)
    {
        $this->assertSame($expected, mediaAclPath($id));
    }

    /**
     * A page-intended exact-ID rule (e.g. wiki:secret.png as a page) must NOT
     * govern a media file with the same ID. The media file's permission is
     * decided solely by its namespace ACL.
     */
    public function test_mediaAclPath_ignores_exact_id_rule()
    {
        global $conf;
        global $AUTH_ACL;
        $conf['useacl'] = 1;

        $AUTH_ACL = [
            '*                  @ALL    8',  // everyone has upload on root
            'wiki:secret.png    @ALL    0',  // page-intended deny on this exact ID
        ];

        // raw-id check (the old buggy pattern) hits the deny rule
        $this->assertEquals(AUTH_NONE, auth_aclcheck('wiki:secret.png', '', []));

        // the helper produces wiki:*, which the deny rule does not match
        $this->assertEquals(AUTH_UPLOAD, auth_aclcheck(mediaAclPath('wiki:secret.png'), '', []));
    }

    /**
     * Namespace-level ACLs must still apply to media via mediaAclPath().
     */
    public function test_mediaAclPath_applies_namespace_rule()
    {
        global $conf;
        global $AUTH_ACL;
        $conf['useacl'] = 1;

        $AUTH_ACL = [
            '*           @ALL    8',
            'private:*   @ALL    0',
        ];

        $this->assertEquals(AUTH_NONE, auth_aclcheck(mediaAclPath('private:image.png'), '', []));
        $this->assertEquals(AUTH_UPLOAD, auth_aclcheck(mediaAclPath('public:image.png'), '', []));
    }

    /**
     * Root-namespace media must still resolve against the root ACL rule.
     */
    public function test_mediaAclPath_root_namespace()
    {
        global $conf;
        global $AUTH_ACL;
        $conf['useacl'] = 1;

        $AUTH_ACL = [
            '*  @ALL  8',
        ];

        $this->assertEquals(AUTH_UPLOAD, auth_aclcheck(mediaAclPath('image.png'), '', []));
    }
}
