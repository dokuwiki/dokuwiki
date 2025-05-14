<?php

namespace dokuwiki\test\Remote;

use dokuwiki\Remote\AccessDeniedException;
use dokuwiki\Remote\Api;
use dokuwiki\Remote\ApiCore;
use dokuwiki\Remote\RemoteException;
use dokuwiki\test\mock\AuthPlugin;


/**
 * Class remoteapicore_test
 */
class ApiCoreTest extends \DokuWikiTest
{

    protected $userinfo;
    protected $oldAuthAcl;
    /** @var  Api */
    protected $remote;

    public function setUp(): void
    {
        // we need a clean setup before each single test:
        \DokuWikiTest::setUpBeforeClass();

        parent::setUp();
        global $conf;
        global $USERINFO;
        global $AUTH_ACL;
        global $auth;
        $this->oldAuthAcl = $AUTH_ACL;
        $this->userinfo = $USERINFO;
        $auth = new AuthPlugin();

        $conf['remote'] = 1;
        $conf['remoteuser'] = '@user';
        $conf['useacl'] = 0;

        $this->remote = new Api();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        global $USERINFO;
        global $AUTH_ACL;

        $USERINFO = $this->userinfo;
        $AUTH_ACL = $this->oldAuthAcl;
    }

    /**
     * Do an assertion that converts to JSON inbetween
     *
     * This lets us compare result objects with arrays
     */
    protected function assertEqualResult($expected, $actual, $msg = '')
    {
        // sort object arrays
        if (is_array($actual) && array_key_exists(0, $actual) && is_object($actual[0])) {
            sort($actual);
            sort($expected);
        }

        $expected = json_decode(json_encode($expected), true);
        $actual = json_decode(json_encode($actual), true);
        $this->assertEquals($expected, $actual, $msg);
    }

    // region info

    // core.getAPIVersion
    public function testGetAPIVersion()
    {
        $this->assertEqualResult(
            ApiCore::API_VERSION,
            $this->remote->call('core.getAPIVersion')
        );
    }

    // core.getWikiVersion
    public function testGetWikiVersion()
    {
        $this->assertEqualResult(
            getVersion(),
            $this->remote->call('core.getWikiVersion')
        );
    }

    // core.getWikiTitle
    public function testGetWikiTitle()
    {
        global $conf;
        $this->assertEqualResult(
            $conf['title'],
            $this->remote->call('core.getWikiTitle')
        );
    }

    // core.getWikiTime
    public function testGetWikiTime()
    {
        $this->assertEqualsWithDelta(
            time(),
            $this->remote->call('core.getWikiTime'),
            1 // allow 1 second difference
        );
    }

    // endregion

    // region user

    // core.login
    public function testLogin()
    {
        $this->markTestIncomplete('Missing test for core.login API Call');
    }

    // core.logoff
    public function testLogoff()
    {
        $this->markTestIncomplete('Missing test for core.logoff API Call');
    }

    // core.whoAmI
    public function testWhoAmI()
    {
        $this->markTestIncomplete('Missing test for core.whoAmI API Call');
    }

    // core.aclCheck -> See also ApiCoreAclCheckTest.php
    public function testAclCheck()
    {
        $id = 'aclpage';

        $this->assertEquals(AUTH_UPLOAD, $this->remote->call('core.aclCheck', ['page' => $id]));

        global $conf;
        global $AUTH_ACL;
        global $USERINFO;
        $conf['useacl'] = 1;
        $_SERVER['REMOTE_USER'] = 'john';
        $USERINFO['grps'] = ['user'];
        $AUTH_ACL = [
            '*                  @ALL           0',
            '*                  @user          2', //edit
        ];

        $this->assertEquals(AUTH_EDIT, $this->remote->call('core.aclCheck', ['page' => $id]));
    }


    // endregion

    // region pages

    // core.listPages
    public function testlistPagesAll()
    {
        // all pages depends on index
        idx_addPage('wiki:syntax');
        idx_addPage('wiki:dokuwiki');

        $file1 = wikiFN('wiki:syntax');
        $file2 = wikiFN('wiki:dokuwiki');

        $expected = [
            [
                'id' => 'wiki:syntax',
                'title' => 'wiki:syntax',
                'permission' => 8,
                'size' => filesize($file1),
                'revision' => filemtime($file1),
                'hash' => md5(io_readFile($file1)),
                'author' => '',
            ],
            [
                'id' => 'wiki:dokuwiki',
                'title' => 'wiki:dokuwiki',
                'permission' => 8,
                'size' => filesize($file2),
                'revision' => filemtime($file2),
                'hash' => md5(io_readFile($file2)),
                'author' => '',
            ]
        ];
        $this->assertEqualResult(
            $expected,
            $this->remote->call(
                'core.listPages',
                [
                    'namespace' => '',
                    'depth' => 0, // 0 for all
                    'hash' => true
                ]
            )
        );
    }

    // core.listPages
    public function testListPagesNamespace()
    {
        $file1 = wikiFN('wiki:syntax');
        $file2 = wikiFN('wiki:dokuwiki');
        // no indexing needed here

        global $conf;
        $conf['useheading'] = 1;

        $expected = [
            [
                'id' => 'wiki:syntax',
                'title' => 'Formatting Syntax',
                'permission' => 8,
                'size' => filesize($file1),
                'revision' => filemtime($file1),
                'hash' => '',
                'author' => '',
            ],
            [
                'id' => 'wiki:dokuwiki',
                'title' => 'DokuWiki',
                'permission' => 8,
                'size' => filesize($file2),
                'revision' => filemtime($file2),
                'hash' => '',
                'author' => '',
            ],
        ];

        $this->assertEqualResult(
            $expected,
            $this->remote->call(
                'core.listPages',
                [
                    'namespace' => 'wiki:',
                    'depth' => 1,
                ]
            )
        );
    }

    // core.searchPages
    public function testSearchPages()
    {
        $id = 'wiki:syntax';
        $file = wikiFN($id);

        idx_addPage($id); //full text search depends on index
        $expected = [
            [
                'id' => $id,
                'score' => 1,
                'revision' => filemtime($file),
                'permission' => 8,
                'size' => filesize($file),
                'snippet' => ' a footnote)) by using double parentheses.

===== <strong class="search_hit">Sectioning</strong> =====

You can use up to five different levels of',
                'title' => 'wiki:syntax',
                'author' => '',
                'hash' => '',
            ]
        ];

        $this->assertEqualResult(
            $expected,
            $this->remote->call(
                'core.searchPages',
                [
                    'query' => 'Sectioning'
                ]
            )
        );
    }

    //core.getRecentPageChanges
    public function testGetRecentPageChanges()
    {
        $_SERVER['REMOTE_USER'] = 'testuser';

        saveWikiText('pageone', 'test', 'test one');
        $rev1 = filemtime(wikiFN('pageone'));
        saveWikiText('pagetwo', 'test', 'test two');
        $rev2 = filemtime(wikiFN('pagetwo'));

        $expected = [
            [
                'id' => 'pageone',
                'revision' => $rev1,
                'author' => 'testuser',
                'sizechange' => 4,
                'summary' => 'test one',
                'type' => 'C',
                'ip' => clientIP(),
            ],
            [
                'id' => 'pagetwo',
                'revision' => $rev2,
                'author' => 'testuser',
                'sizechange' => 4,
                'summary' => 'test two',
                'type' => 'C',
                'ip' => clientIP(),
            ]
        ];

        $this->assertEqualResult(
            $expected,
            $this->remote->call(
                'core.getRecentPageChanges',
                [
                    'timestamp' => 0 // all recent changes
                ]
            )
        );
    }

    // core.getPage
    public function testGetPage()
    {
        $id = 'pageversion';
        $file = wikiFN($id);

        saveWikiText($id, 'first version', 'first');
        $rev1 = filemtime($file);
        clearstatcache(false, $file);
        $this->waitForTick(true);
        saveWikiText($id, 'second version', 'second');
        $rev2 = filemtime($file);

        $this->assertEqualResult(
            'second version',
            $this->remote->call('core.getPage', ['page' => $id, 'rev' => 0]),
            'no revision given -> current'
        );

        $this->assertEqualResult(
            'first version',
            $this->remote->call('core.getPage', ['page' => $id, 'rev' => $rev1]),
            '1st revision given'
        );

        $this->assertEqualResult(
            'second version',
            $this->remote->call('core.getPage', ['page' => $id, 'rev' => $rev2]),
            '2nd revision given'
        );

        $this->assertEqualResult(
            '',
            $this->remote->call('core.getPage', ['page' => $id, 'rev' => 1234]),
            'Non existing revision given'
        );

        $this->assertEqualResult(
            '',
            $this->remote->call('core.getPage', ['page' => 'foobar', 'rev' => 1234]),
            'Non existing page given'
        );
    }

    //core.getPageHTML
    public function testGetPageHTMLVersion()
    {
        $id = 'htmltest';
        $file = wikiFN($id);

        $content1 = "====Title====\nText";
        $html1 = "\n<h3 class=\"sectionedit1\" id=\"title\">Title</h3>\n<div class=\"level3\">\n\n<p>\nText\n</p>\n\n</div>\n";
        $content2 = "====Foobar====\nText Bamm";
        $html2 = "\n<h3 class=\"sectionedit1\" id=\"foobar\">Foobar</h3>\n<div class=\"level3\">\n\n<p>\nText Bamm\n</p>\n\n</div>\n";

        saveWikiText($id, $content1, 'first');
        $rev1 = filemtime($file);
        clearstatcache(false, $file);
        $this->waitForTick(true);
        saveWikiText($id, $content2, 'second');
        $rev2 = filemtime($file);

        $this->assertEqualResult(
            $html2,
            $this->remote->call('core.getPageHTML', ['page' => $id, 'rev' => 0]),
            'no revision given -> current'
        );

        $this->assertEqualResult(
            $html1,
            $this->remote->call('core.getPageHTML', ['page' => $id, 'rev' => $rev1]),
            '1st revision given'
        );

        $this->assertEqualResult(
            $html2,
            $this->remote->call('core.getPageHTML', ['page' => $id, 'rev' => $rev2]),
            '2nd revision given'
        );

        $e = null;
        try {
            $this->remote->call('core.getPageHTML', ['page' => $id, 'rev' => 1234]);
        } catch (RemoteException $e) {
        }
        $this->assertInstanceOf(RemoteException::class, $e);
        $this->assertEquals(121, $e->getCode(), 'Non existing revision given');

        $e = null;
        try {
            $this->remote->call('core.getPageHTML', ['page' => 'foobar', 'rev' => 1234]);
        } catch (RemoteException $e) {
        }
        $this->assertInstanceOf(RemoteException::class, $e);
        $this->assertEquals(121, $e->getCode(), 'Non existing page given');
    }

    //core.getPageInfo
    public function testGetPageInfo()
    {
        $id = 'pageinfo';
        $file = wikiFN($id);

        $_SERVER['REMOTE_USER'] = 'testuser';

        saveWikiText($id, 'first version', 'first');
        $rev1 = filemtime($file);
        clearstatcache(false, $file);
        $this->waitForTick(true);
        saveWikiText($id, 'second version', 'second');
        $rev2 = filemtime($file);

        $expected = [
            'id' => $id,
            'revision' => $rev2,
            'author' => 'testuser',
            'hash' => md5(io_readFile($file)),
            'title' => $id,
            'size' => filesize($file),
            'permission' => 8,
        ];
        $this->assertEqualResult(
            $expected,
            $this->remote->call('core.getPageInfo', ['page' => $id, 'rev' => 0, 'hash' => true, 'author' => true]),
            'no revision given -> current'
        );

        $expected = [
            'id' => $id,
            'revision' => $rev1,
            'author' => '',
            'hash' => '',
            'title' => $id,
            'size' => filesize(wikiFN($id, $rev1)),
            'permission' => 8,
        ];
        $this->assertEqualResult(
            $expected,
            $this->remote->call('core.getPageInfo', ['page' => $id, 'rev' => $rev1]),
            '1st revision given'
        );

        $expected = [
            'id' => $id,
            'revision' => $rev2,
            'author' => '',
            'hash' => '',
            'title' => $id,
            'size' => filesize(wikiFN($id, $rev2)),
            'permission' => 8,
        ];
        $this->assertEqualResult(
            $expected,
            $this->remote->call('core.getPageInfo', ['page' => $id, 'rev' => $rev2]),
            '2nd revision given'
        );

        $e = null;
        try {
            $this->remote->call('core.getPageInfo', ['page' => $id, 'rev' => 1234]);
        } catch (RemoteException $e) {
        }
        $this->assertInstanceOf(RemoteException::class, $e);
        $this->assertEquals(121, $e->getCode(), 'Non existing revision given');

        $e = null;
        try {
            $this->remote->call('core.getPageInfo', ['page' => 'foobar', 'rev' => 1234]);
        } catch (RemoteException $e) {
        }
        $this->assertInstanceOf(RemoteException::class, $e);
        $this->assertEquals(121, $e->getCode(), 'Non existing page given');
    }

    //core.getPageHistory
    public function testGetPageHistory()
    {
        global $conf;

        $id = 'revpage';
        $file = wikiFN($id);

        $rev = [];
        for ($i = 0; $i < 6; $i++) {
            $this->waitForTick();
            saveWikiText($id, "rev$i", "rev$i");
            clearstatcache(false, $file);
            $rev[$i] = filemtime($file);
        }

        $params = ['page' => $id, 'first' => 0];
        $versions = $this->remote->call('core.getPageHistory', $params);
        $versions = json_decode(json_encode($versions), true);
        $this->assertEquals(6, count($versions));
        $this->assertEquals($rev[5], $versions[0]['revision']);
        $this->assertEquals($rev[4], $versions[1]['revision']);
        $this->assertEquals($rev[3], $versions[2]['revision']);
        $this->assertEquals($rev[2], $versions[3]['revision']);
        $this->assertEquals($rev[1], $versions[4]['revision']);
        $this->assertEquals($rev[0], $versions[5]['revision']);

        $params = ['page' => $id, 'first' => 1]; // offset 1
        $versions = $this->remote->call('core.getPageHistory', $params);
        $versions = json_decode(json_encode($versions), true);
        $this->assertEquals(5, count($versions));
        $this->assertEquals($rev[4], $versions[0]['revision']);
        $this->assertEquals($rev[3], $versions[1]['revision']);
        $this->assertEquals($rev[2], $versions[2]['revision']);
        $this->assertEquals($rev[1], $versions[3]['revision']);
        $this->assertEquals($rev[0], $versions[4]['revision']);

        $conf['recent'] = 3; //set number of results per page

        $params = ['page' => $id, 'first' => 0]; // first page
        $versions = $this->remote->call('core.getPageHistory', $params);
        $versions = json_decode(json_encode($versions), true);
        $this->assertEquals(3, count($versions));
        $this->assertEquals($rev[5], $versions[0]['revision']);
        $this->assertEquals($rev[4], $versions[1]['revision']);
        $this->assertEquals($rev[3], $versions[2]['revision']);

        $params = ['page' => $id, 'first' => $conf['recent']]; // second page
        $versions = $this->remote->call('core.getPageHistory', $params);
        $versions = json_decode(json_encode($versions), true);
        $this->assertEquals(3, count($versions));
        $this->assertEquals($rev[2], $versions[0]['revision']);
        $this->assertEquals($rev[1], $versions[1]['revision']);
        $this->assertEquals($rev[0], $versions[2]['revision']);

        $params = ['page' => $id, 'first' => $conf['recent'] * 2]; // third page
        $versions = $this->remote->call('core.getPageHistory', $params);
        $versions = json_decode(json_encode($versions), true);
        $this->assertEquals(0, count($versions));
    }

    //core.getPageLinks
    public function testGetPageLinks()
    {
        $localdoku = [
            'type' => 'local',
            'page' => 'DokuWiki',
            'href' => DOKU_BASE . DOKU_SCRIPT . '?id=DokuWiki'
        ];
        $expected = [
            $localdoku,
            [
                'type' => 'extern',
                'page' => 'http://www.freelists.org',
                'href' => 'http://www.freelists.org'
            ],
            [
                'type' => 'interwiki',
                'page' => 'rfc>1855',
                'href' => 'https://tools.ietf.org/html/rfc1855'
            ],
            [
                'type' => 'extern',
                'page' => 'http://www.catb.org/~esr/faqs/smart-questions.html',
                'href' => 'http://www.catb.org/~esr/faqs/smart-questions.html'
            ],
            $localdoku,
            $localdoku
        ];

        $this->assertEqualResult(
            $expected,
            $this->remote->call('core.getPageLinks', ['page' => 'mailinglist'])
        );

        $this->expectExceptionCode(121);
        $this->remote->call('core.getPageLinks', ['page' => 'foobar']);
    }

    //core.getPageBackLinks
    public function testGetPageBackLinks()
    {
        saveWikiText('linky', '[[wiki:syntax]]', 'test');
        // backlinks need index
        idx_addPage('wiki:syntax');
        idx_addPage('linky');

        $result = $this->remote->call('core.getPageBackLinks', ['page' => 'wiki:syntax']);
        $this->assertTrue(count($result) > 0);
        $this->assertEqualResult(ft_backlinks('wiki:syntax'), $result);

        $this->assertEquals([], $this->remote->call('core.getPageBackLinks', ['page' => 'foobar']));
    }

    //core.lockPages
    public function testLockPages()
    {
        // lock a first set of pages
        $_SERVER['REMOTE_USER'] = 'testuser1';
        $tolock = ['wiki:dokuwiki', 'nonexisting'];
        $this->assertEquals(
            $tolock,
            $this->remote->call('core.lockPages', ['pages' => $tolock]),
            'all pages should lock'
        );

        // now we're someone else
        $_SERVER['REMOTE_USER'] = 'testuser2';
        $tolock = ['wiki:dokuwiki', 'nonexisting', 'wiki:syntax', 'another'];
        $expected = ['wiki:syntax', 'another'];
        $this->assertEquals(
            $expected,
            $this->remote->call('core.lockPages', ['pages' => $tolock]),
            'only half the pages should lock'
        );
    }

    // core.unlockPages
    public function testUnlockPages()
    {
        $_SERVER['REMOTE_USER'] = 'testuser1';
        lock('wiki:dokuwiki');
        lock('nonexisting');

        $_SERVER['REMOTE_USER'] = 'testuser2';
        lock('wiki:syntax');
        lock('another');

        $tounlock = ['wiki:dokuwiki', 'nonexisting', 'wiki:syntax', 'another', 'notlocked'];
        $expected = ['wiki:syntax', 'another'];

        $this->assertEquals(
            $expected,
            $this->remote->call('core.unlockPages', ['pages' => $tounlock])
        );
    }

    //core.savePage
    public function testSavePage()
    {
        $id = 'putpage';

        $content = "====Title====\nText";
        $params = [
            'page' => $id,
            'text' => $content,
            'isminor' => false,
            'summary' => 'Summary of nice text'
        ];
        $this->assertTrue($this->remote->call('core.savePage', $params));
        $this->assertEquals($content, rawWiki($id));

        // remove page
        $params = [
            'page' => $id,
            'text' => '',
        ];
        $this->assertTrue($this->remote->call('core.savePage', $params));
        $this->assertFileNotExists(wikiFN($id));

        // remove non existing page (reusing above params)
        $e = null;
        try {
            $this->remote->call('core.savePage', $params);
        } catch (RemoteException $e) {
        }
        $this->assertInstanceOf(RemoteException::class, $e);
        $this->assertEquals(132, $e->getCode());
    }

    //core.appendPage
    public function testAppendPage()
    {
        $id = 'appendpage';
        $content = 'a test';
        $morecontent = "\nOther text";
        saveWikiText($id, $content, 'local');

        $params = [
            'page' => $id,
            'text' => $morecontent,
        ];
        $this->assertEquals(true, $this->remote->call('core.appendPage', $params));
        $this->assertEquals($content . $morecontent, rawWiki($id));
    }

    // endregion

    // region media

    // core.listMedia
    public function testListMedia()
    {
        $id = 'wiki:dokuwiki-128.png';
        $file = mediaFN($id);
        $content = file_get_contents($file);

        $expected = [
            [
                'id' => $id,
                'size' => filesize($file),
                'revision' => filemtime($file),
                'isimage' => true,
                'hash' => md5($content),
                'permission' => 8,
                'author' => '',
            ]
        ];
        $this->assertEqualResult(
            $expected,
            $this->remote->call(
                'core.listMedia',
                [
                    'namespace' => 'wiki',
                    'pattern' => '/128/',
                    'hash' => true,
                ]
            )
        );
    }

    //core.getRecentMediaChanges
    public function testGetRecentMediaChanges()
    {
        global $conf;

        $_SERVER['REMOTE_USER'] = 'testuser';

        $orig = mediaFN('wiki:dokuwiki-128.png');
        $tmp = $conf['tmpdir'] . 'test.png';

        $target1 = 'test:image1.png';
        $file1 = mediaFN($target1);
        copy($orig, $tmp);
        media_save(['name' => $tmp], $target1, true, AUTH_UPLOAD, 'rename');

        $target2 = 'test:image2.png';
        $file2 = mediaFN($target2);
        copy($orig, $tmp);
        media_save(['name' => $tmp], $target2, true, AUTH_UPLOAD, 'rename');

        $expected = [
            [
                'id' => $target1,
                'revision' => filemtime($file1),
                'author' => 'testuser',
                'ip' => clientIP(),
                'sizechange' => filesize($file1),
                'summary' => 'created',
                'type' => 'C',
            ],
            [
                'id' => $target2,
                'revision' => filemtime($file2),
                'author' => 'testuser',
                'ip' => clientIP(),
                'sizechange' => filesize($file2),
                'summary' => 'created',
                'type' => 'C',
            ]
        ];

        $this->assertEqualResult(
            $expected,
            $this->remote->call(
                'core.getRecentMediaChanges',
                [
                    'timestamp' => 0 // all recent changes
                ]
            )
        );
    }

    //core.getMedia
    public function testGetMedia()
    {
        $id = 'wiki:dokuwiki-128.png';
        $file = mediaFN($id);
        $base64 = base64_encode(file_get_contents($file));

        $this->assertEquals(
            $base64,
            $this->remote->call('core.getMedia', ['media' => $id])
        );

        $e = null;
        try {
            $this->remote->call('core.getMedia', ['media' => $id, 'rev' => 1234]);
        } catch (RemoteException $e) {
        }
        $this->assertInstanceOf(RemoteException::class, $e);
        $this->assertEquals(221, $e->getCode(), 'Non existing revision given');

        $e = null;
        try {
            $this->remote->call('core.getMedia', ['media' => 'foobar.png']);
        } catch (RemoteException $e) {
        }
        $this->assertInstanceOf(RemoteException::class, $e);
        $this->assertEquals(221, $e->getCode(), 'Non existing media id given');
    }


    //core.getMediaInfo
    public function testGetMediaInfo()
    {
        $id = 'wiki:dokuwiki-128.png';
        $file = mediaFN($id);

        $expected = [
            'id' => $id,
            'revision' => filemtime($file),
            'author' => '',
            'hash' => md5(file_get_contents($file)),
            'size' => filesize($file),
            'permission' => 8,
            'isimage' => true,
        ];
        $this->assertEqualResult(
            $expected,
            $this->remote->call('core.getMediaInfo', ['media' => $id, 'hash' => true, 'author' => false])
        );

        $e = null;
        try {
            $this->remote->call('core.getMediaInfo', ['media' => $id, 'rev' => 1234]);
        } catch (RemoteException $e) {
        }
        $this->assertInstanceOf(RemoteException::class, $e);
        $this->assertEquals(221, $e->getCode(), 'Non existing revision given');

        $e = null;
        try {
            $this->remote->call('core.getMediaInfo', ['media' => 'foobar.png']);
        } catch (RemoteException $e) {
        }
        $this->assertInstanceOf(RemoteException::class, $e);
        $this->assertEquals(221, $e->getCode(), 'Non existing media id given');
    }

    //core.saveMedia
    public function testSaveMedia()
    {
        $orig = mediaFN('wiki:dokuwiki-128.png');
        $base64 = base64_encode(file_get_contents($orig));

        $target = 'test:putimage.png';
        $targetfile = mediaFN($target);

        $this->assertTrue($this->remote->call('core.saveMedia', ['media' => $target, 'base64' => $base64]));
        $this->assertFileExists($targetfile);
        $this->assertFileEquals($orig, $targetfile);
    }

    //core.deleteMedia
    public function testDeleteMedia()
    {
        global $conf;
        global $AUTH_ACL;
        global $USERINFO;

        $id = 'wiki:dokuwiki-128.png';
        $file = mediaFN($id);

        // deletion should fail, we only have AUTH_UPLOAD
        $e = null;
        try {
            $this->remote->call('core.deleteMedia', ['media' => $id]);
        } catch (AccessDeniedException $e) {
        }
        $this->assertInstanceOf(AccessDeniedException::class, $e);
        $this->assertEquals(212, $e->getCode(), 'No permission to delete');
        $this->assertFileExists($file);

        // setup new ACLs
        $conf['useacl'] = 1;
        $_SERVER['REMOTE_USER'] = 'john';
        $USERINFO['grps'] = array('user');
        $AUTH_ACL = array(
            '*                  @ALL           0',
            '*                  @user          16',
        );

        // deletion should work now
        $this->assertTrue($this->remote->call('core.deleteMedia', ['media' => $id]));
        $this->assertFileNotExists($file);

        clearstatcache(false, $file);

        // deleting the file again should not work
        $e = null;
        try {
            $this->remote->call('core.deleteMedia', ['media' => $id]);
        } catch (RemoteException $e) {
        }
        $this->assertInstanceOf(RemoteException::class, $e);
        $this->assertEquals(221, $e->getCode(), 'Non existing media id given');
    }
    // endregion
}
