<?php

namespace dokuwiki\test\Remote;

use dokuwiki\Extension\Event;
use dokuwiki\Remote\Api;
use dokuwiki\Remote\ApiCore;
use dokuwiki\test\mock\AuthDeletePlugin;
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

    /** Delay writes of old revisions by a second. */
    public function handle_write(Event $event, $param)
    {
        if ($event->data[3] !== false) {
            $this->waitForTick();
        }
    }

    public function testGetVersion()
    {
        $this->assertEquals(getVersion(), $this->remote->call('dokuwiki.getVersion'));
    }

    public function testGetPageList()
    {
        $file1 = wikiFN('wiki:dokuwiki');
        $file2 = wikiFN('wiki:syntax');
        $expected = [
            [
                'id' => 'wiki:dokuwiki',
                'rev' => filemtime($file1),
                'mtime' => filemtime($file1),
                'size' => filesize($file1),
                'hash' => md5(trim(rawWiki('wiki:dokuwiki')))
            ],
            [
                'id' => 'wiki:syntax',
                'rev' => filemtime($file2),
                'mtime' => filemtime($file2),
                'size' => filesize($file2),
                'hash' => md5(trim(rawWiki('wiki:syntax')))
            ]
        ];
        $params = [
            'wiki:',
            [
                'depth' => 0, // 0 for all
                'hash' => 1,
                'skipacl' => 1 // is ignored
            ]
        ];
        $this->assertEquals($expected, $this->remote->call('dokuwiki.getPagelist', $params));
    }

    public function testSearch()
    {
        $id = 'wiki:syntax';
        $file = wikiFN($id);

        idx_addPage($id); //full text search depends on index
        $expected = [
            [
                'id' => $id,
                'score' => 1,
                'rev' => filemtime($file),
                'mtime' => filemtime($file),
                'size' => filesize($file),
                'snippet' => ' a footnote)) by using double parentheses.

===== <strong class="search_hit">Sectioning</strong> =====

You can use up to five different levels of',
                'title' => 'wiki:syntax'
            ]
        ];
        $params = ['Sectioning'];
        $this->assertEquals($expected, $this->remote->call('dokuwiki.search', $params));
    }

    public function testGetTime()
    {
        $timeexpect = time();
        $timeactual = $this->remote->call('dokuwiki.getTime');
        $this->assertTrue(($timeexpect <= $timeactual) && ($timeactual <= $timeexpect + 1));
    }

    public function testSetLocks()
    {
        $expected = [
            'locked' => ['wiki:dokuwiki', 'wiki:syntax', 'nonexisting'],
            'lockfail' => [],
            'unlocked' => [],
            'unlockfail' => [],
        ];
        $params = [
            [
                'lock' => ['wiki:dokuwiki', 'wiki:syntax', 'nonexisting'],
                'unlock' => []
            ]
        ];
        $this->assertEquals($expected, $this->remote->call('dokuwiki.setLocks', $params));

        $expected = [
            'locked' => [],
            'lockfail' => [],
            'unlocked' => ['wiki:dokuwiki', 'wiki:syntax', 'nonexisting'],
            'unlockfail' => ['nonexisting2'],
        ];
        $params = [
            [
                'lock' => [],
                'unlock' => ['wiki:dokuwiki', 'wiki:syntax', 'nonexisting', 'nonexisting2']
            ]
        ];
        $this->assertEquals($expected, $this->remote->call('dokuwiki.setLocks', $params));
    }

    public function testGetTitle()
    {
        global $conf;
        $this->assertEquals($conf['title'], $this->remote->call('dokuwiki.getTitle'));
    }

    public function testPutPage()
    {
        $id = 'putpage';

        $content = "====Title====\nText";
        $params = [
            $id,
            $content,
            [
                'minor' => false,
                'sum' => 'Summary of nice text'
            ]
        ];
        $this->assertTrue($this->remote->call('wiki.putPage', $params));
        $this->assertEquals($content, rawWiki($id));

        //remove page
        $params = [
            $id,
            '',
            [
                'minor' => false,
            ]
        ];
        $this->assertTrue($this->remote->call('wiki.putPage', $params));
        $this->assertFileNotExists(wikiFN($id));
    }

    public function testGetPage()
    {
        $id = 'getpage';
        $content = 'a test';
        saveWikiText($id, $content, 'test for getpage');

        $params = [$id];
        $this->assertEquals($content, $this->remote->call('wiki.getPage', $params));
    }

    public function testAppendPage()
    {
        $id = 'appendpage';
        $content = 'a test';
        $morecontent = "\nOther text";
        saveWikiText($id, $content, 'local');

        $params = [
            $id,
            $morecontent,
            []
        ];
        $this->assertEquals(true, $this->remote->call('dokuwiki.appendPage', $params));
        $this->assertEquals($content . $morecontent, rawWiki($id));
    }

    public function testGetPageVersion()
    {
        $id = 'pageversion';
        $file = wikiFN($id);

        saveWikiText($id, 'first version', 'first');
        $rev1 = filemtime($file);
        clearstatcache(false, $file);
        $this->waitForTick(true);
        saveWikiText($id, 'second version', 'second');
        $rev2 = filemtime($file);

        $params = [$id, ''];
        $this->assertEquals('second version', $this->remote->call('wiki.getPageVersion', $params), 'no revision given');

        $params = [$id, $rev1];
        $this->assertEquals('first version', $this->remote->call('wiki.getPageVersion', $params), '1st revision given');

        $params = [$id, $rev2];
        $this->assertEquals('second version', $this->remote->call('wiki.getPageVersion', $params), '2nd revision given');

        $params = [$id, 1234];
        $this->assertEquals('', $this->remote->call('wiki.getPageVersion', $params), 'Non existing revision given');

        $params = ['foobar', 1234];
        $this->assertEquals('', $this->remote->call('wiki.getPageVersion', $params), 'Non existing page given');
    }

    public function testGetPageHTML()
    {
        $id = 'htmltest';
        $content = "====Title====\nText";
        $html = "\n<h3 class=\"sectionedit1\" id=\"title\">Title</h3>\n<div class=\"level3\">\n\n<p>\nText\n</p>\n\n</div>\n";

        saveWikiText($id, $content, 'htmltest');

        $params = [$id];
        $this->assertEquals($html, $this->remote->call('wiki.getPageHTML', $params));
    }

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

        $params = [$id, ''];
        $this->assertEquals($html2, $this->remote->call('wiki.getPageHTMLVersion', $params), 'no revision given');

        $params = [$id, $rev1];
        $this->assertEquals($html1, $this->remote->call('wiki.getPageHTMLVersion', $params), '1st revision given');

        $params = [$id, $rev2];
        $this->assertEquals($html2, $this->remote->call('wiki.getPageHTMLVersion', $params), '2nd revision given');

        $params = [$id, 1234];
        $this->assertEquals('', $this->remote->call('wiki.getPageHTMLVersion', $params), 'Non existing revision given');

        $params = ['foobar', 1234];
        $this->assertEquals('', $this->remote->call('wiki.getPageHTMLVersion', $params), 'Non existing page given');
    }

    public function testGetAllPages()
    {
        // all pages depends on index
        idx_addPage('wiki:syntax');
        idx_addPage('wiki:dokuwiki');

        $file1 = wikiFN('wiki:syntax');
        $file2 = wikiFN('wiki:dokuwiki');

        $expected = [
            [
                'id' => 'wiki:syntax',
                'perms' => 8,
                'size' => filesize($file1),
                'lastModified' => filemtime($file1)
            ],
            [
                'id' => 'wiki:dokuwiki',
                'perms' => 8,
                'size' => filesize($file2),
                'lastModified' => filemtime($file2)
            ]
        ];
        $this->assertEquals($expected, $this->remote->call('wiki.getAllPages'));
    }

    public function testGetBacklinks()
    {
        saveWikiText('linky', '[[wiki:syntax]]', 'test');
        // backlinks need index
        idx_addPage('wiki:syntax');
        idx_addPage('linky');

        $params = ['wiki:syntax'];
        $result = $this->remote->call('wiki.getBackLinks', $params);
        $this->assertTrue(count($result) > 0);
        $this->assertEquals(ft_backlinks('wiki:syntax'), $result);
    }

    public function testGetPageInfo()
    {
        $id = 'pageinfo';
        $file = wikiFN($id);

        saveWikiText($id, 'test', 'test');

        $expected = [
            'name' => $id,
            'lastModified' => filemtime($file),
            'author' => clientIP(),
            'version' => filemtime($file)
        ];
        $params = [$id];
        $this->assertEquals($expected, $this->remote->call('wiki.getPageInfo', $params));
    }

    public function testGetPageInfoVersion()
    {
        $id = 'pageinfo';
        $file = wikiFN($id);

        saveWikiText($id, 'first version', 'first');
        $rev1 = filemtime($file);
        clearstatcache(false, $file);
        $this->waitForTick(true);
        saveWikiText($id, 'second version', 'second');
        $rev2 = filemtime($file);

        $expected = [
            'name' => $id,
            'lastModified' => $rev2,
            'author' => clientIP(),
            'version' => $rev2
        ];
        $params = [$id, ''];
        $this->assertEquals($expected, $this->remote->call('wiki.getPageInfoVersion', $params), 'no revision given');

        $expected = [
            'name' => $id,
            'lastModified' => $rev1,
            'author' => clientIP(),
            'version' => $rev1
        ];
        $params = [$id, $rev1];
        $this->assertEquals($expected, $this->remote->call('wiki.getPageInfoVersion', $params), '1st revision given');

        $expected = [
            'name' => $id,
            'lastModified' => $rev2,
            'author' => clientIP(),
            'version' => $rev2
        ];
        $params = [$id, $rev2];
        $this->assertEquals($expected, $this->remote->call('wiki.getPageInfoVersion', $params), '2nd revision given');
    }

    public function testGetRecentChanges()
    {

        saveWikiText('pageone', 'test', 'test');
        $rev1 = filemtime(wikiFN('pageone'));
        saveWikiText('pagetwo', 'test', 'test');
        $rev2 = filemtime(wikiFN('pagetwo'));

        $expected = [
            [
                'name' => 'pageone',
                'lastModified' => $rev1,
                'author' => '',
                'version' => $rev1,
                'perms' => 8,
                'size' => 4
            ],
            [
                'name' => 'pagetwo',
                'lastModified' => $rev2,
                'author' => '',
                'version' => $rev2,
                'perms' => 8,
                'size' => 4
            ]
        ];
        $params = [strtotime("-1 year")];
        $this->assertEquals($expected, $this->remote->call('wiki.getRecentChanges', $params));
    }

    public function testGetPageVersions()
    {
        /** @var $EVENT_HANDLER \dokuwiki\Extension\EventHandler */
        global $EVENT_HANDLER;
        $EVENT_HANDLER->register_hook('IO_WIKIPAGE_WRITE', 'BEFORE', $this, 'handle_write');
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

        $params = [$id, 0];
        $versions = $this->remote->call('wiki.getPageVersions', $params);
        $this->assertEquals(6, count($versions));
        $this->assertEquals($rev[5], $versions[0]['version']);
        $this->assertEquals($rev[4], $versions[1]['version']);
        $this->assertEquals($rev[3], $versions[2]['version']);
        $this->assertEquals($rev[2], $versions[3]['version']);
        $this->assertEquals($rev[1], $versions[4]['version']);
        $this->assertEquals($rev[0], $versions[5]['version']);

        $params = [$id, 1]; // offset 1
        $versions = $this->remote->call('wiki.getPageVersions', $params);
        $this->assertEquals(5, count($versions));
        $this->assertEquals($rev[4], $versions[0]['version']);
        $this->assertEquals($rev[3], $versions[1]['version']);
        $this->assertEquals($rev[2], $versions[2]['version']);
        $this->assertEquals($rev[1], $versions[3]['version']);
        $this->assertEquals($rev[0], $versions[4]['version']);

        $conf['recent'] = 3; //set number of results per page

        $params = [$id, 0]; // first page
        $versions = $this->remote->call('wiki.getPageVersions', $params);
        $this->assertEquals(3, count($versions));
        $this->assertEquals($rev[5], $versions[0]['version']);
        $this->assertEquals($rev[4], $versions[1]['version']);
        $this->assertEquals($rev[3], $versions[2]['version']);

        $params = [$id, $conf['recent']]; // second page
        $versions = $this->remote->call('wiki.getPageVersions', $params);
        $this->assertEquals(3, count($versions));
        $this->assertEquals($rev[2], $versions[0]['version']);
        $this->assertEquals($rev[1], $versions[1]['version']);
        $this->assertEquals($rev[0], $versions[2]['version']);

        $params = [$id, $conf['recent'] * 2]; // third page
        $versions = $this->remote->call('wiki.getPageVersions', $params);
        $this->assertEquals(0, count($versions));
    }

    public function testDeleteUser()
    {
        global $conf, $auth;
        $auth = new AuthDeletePlugin();
        $conf['remote'] = 1;
        $conf['remoteuser'] = 'testuser';
        $_SERVER['REMOTE_USER'] = 'testuser';
        $params = [
            ['testuser']
        ];
        $actualCallResult = $this->remote->call('dokuwiki.deleteUsers', $params);
        $this->assertTrue($actualCallResult);
    }

    public function testAclCheck()
    {
        $id = 'aclpage';

        $params = [$id];
        $this->assertEquals(AUTH_UPLOAD, $this->remote->call('wiki.aclCheck', $params));

        global $conf;
        global $AUTH_ACL, $USERINFO;
        $conf['useacl'] = 1;
        $_SERVER['REMOTE_USER'] = 'john';
        $USERINFO['grps'] = ['user'];
        $AUTH_ACL = [
            '*                  @ALL           0',
            '*                  @user          2', //edit
        ];

        $params = [$id];
        $this->assertEquals(AUTH_EDIT, $this->remote->call('wiki.aclCheck', $params));
    }

    public function testGetXMLRPCAPIVersion()
    {
        $this->assertEquals(ApiCore::API_VERSION, $this->remote->call('dokuwiki.getXMLRPCAPIVersion'));
    }

    public function testGetRPCVersionSupported()
    {
        $this->assertEquals(2, $this->remote->call('wiki.getRPCVersionSupported'));
    }

    public function testListLinks()
    {
        $localdoku = [
            'type' => 'local',
            'page' => 'DokuWiki',
            'href' => DOKU_BASE . DOKU_SCRIPT . '?id=DokuWiki'
        ];
        $expected = [  //no local links
            $localdoku,
            [
                'type' => 'extern',
                'page' => 'http://www.freelists.org',
                'href' => 'http://www.freelists.org'
            ],
            [
                'type' => 'extern',
                'page' => 'https://tools.ietf.org/html/rfc1855',
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
        $params = ['mailinglist'];
        $this->assertEquals($expected, $this->remote->call('wiki.listLinks', $params));
    }

    public function testCoreattachments()
    {
        global $conf;
        global $AUTH_ACL, $USERINFO;

        $filecontent = io_readFile(mediaFN('wiki:dokuwiki-128.png'), false);
        $params = ['test:dokuwiki-128_2.png', $filecontent, ['ow' => false]];
        $this->assertEquals('test:dokuwiki-128_2.png', $this->remote->call('wiki.putAttachment', $params)); //prints a success div

        $params = ['test:dokuwiki-128_2.png'];
        $this->assertEquals($filecontent, $this->remote->call('wiki.getAttachment', $params));
        $rev = filemtime(mediaFN('test:dokuwiki-128_2.png'));

        $expected = [
            'lastModified' => $rev,
            'size' => 27895,
        ];
        $params = ['test:dokuwiki-128_2.png'];
        $this->assertEquals($expected, $this->remote->call('wiki.getAttachmentInfo', $params));

        $params = [strtotime("-5 year")];
        $expected = [
            [
                'name' => 'test:dokuwiki-128_2.png',
                'lastModified' => $rev,
                'author' => '',
                'version' => $rev,
                'perms' => 8,
                'size' => 27895 //actual size, not size change
            ]
        ];
        $this->assertEquals($expected, $this->remote->call('wiki.getRecentMediaChanges', $params));

        $this->waitForTick(true);
        $conf['useacl'] = 1;
        $_SERVER['REMOTE_USER'] = 'john';
        $USERINFO['grps'] = ['user'];
        $AUTH_ACL = [
            '*                  @ALL           0',
            '*                  @user          16',
        ];

        $params = ['test:dokuwiki-128_2.png'];
        $this->assertEquals(0, $this->remote->call('wiki.deleteAttachment', $params));

        $rev2 = filemtime($conf['media_changelog']);
        $expected = [
            'lastModified' => $rev2,
            'size' => 0,
        ];
        $params = ['test:dokuwiki-128_2.png'];
        $this->assertEquals($expected, $this->remote->call('wiki.getAttachmentInfo', $params));

        $expected = [
            'lastModified' => 0,
            'size' => 0,
        ];
        $params = ['test:nonexisting.png'];
        $this->assertEquals($expected, $this->remote->call('wiki.getAttachmentInfo', $params));

        $media1 = mediaFN('wiki:dokuwiki-128.png');
        $expected = [
            [
                'id' => 'wiki:dokuwiki-128.png',
                'file' => 'dokuwiki-128.png',
                'size' => filesize($media1),
                'mtime' => filemtime($media1),
                'writable' => 1,
                'isimg' => 1,
                'hash' => md5(io_readFile($media1, false)),
                'perms' => 16,
                'lastModified' => filemtime($media1)
            ]
        ];
        $params = [
            'wiki:',
            [
                'depth' => 0, // 0 for all
                'hash' => 1,
                'skipacl' => 1, // is ignored
                'showmsg' => true, //useless??
                'pattern' => '/128/' //filter
            ]
        ];
        $this->assertEquals($expected, $this->remote->call('wiki.getAttachments', $params));
    }

}
