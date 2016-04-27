<?php

/**
 * Class remoteapicore_test
 */
class remoteapicore_test extends DokuWikiTest {

    var $userinfo;
    var $oldAuthAcl;
    /** @var  RemoteAPI */
    var $remote;

    function setUp() {
        parent::setUp();
        global $conf;
        global $USERINFO;
        global $AUTH_ACL;
        global $auth;
        $this->oldAuthAcl = $AUTH_ACL;
        $this->userinfo = $USERINFO;
        $auth = new DokuWiki_Auth_Plugin();

        $conf['remote'] = 1;
        $conf['remoteuser'] = '@user';
        $conf['useacl'] = 0;

        $this->remote = new RemoteAPI();
    }

    function tearDown() {
        global $USERINFO;
        global $AUTH_ACL;

        $USERINFO = $this->userinfo;
        $AUTH_ACL = $this->oldAuthAcl;

    }

    function test_core() {
        $remoteApi = new RemoteApi();

        $this->assertEquals(getVersion(), $remoteApi->call('dokuwiki.getVersion'));
//        $params = array('user', 'passwrd');
//        $this->assertEquals(, $remoteApi->call('dokuwiki.login'));                   //TODO

//        $this->assertEquals(, $remoteApi->call('dokuwiki.logoff'));                  //TODO

        $file1 = wikiFN('wiki:dokuwiki');
        $file2 = wikiFN('wiki:syntax');
        $expected = array(
            array(
                'id' => 'wiki:dokuwiki',
                'rev' => filemtime($file1),
                'mtime' => filemtime($file1),
                'size' => filesize($file1),
                'hash' => md5(trim(rawWiki('wiki:dokuwiki')))
            ),
            array(
                'id' => 'wiki:syntax',
                'rev' => filemtime($file2),
                'mtime' => filemtime($file2),
                'size' => filesize($file2),
                'hash' => md5(trim(rawWiki('wiki:syntax')))
            )
        );
        $params = array(
            'wiki:',
            array(
                'depth' => 0, // 0 for all
                'hash' => 1,
                'skipacl' => 1 // is ignored
            )
        );
        $this->assertEquals($expected, $remoteApi->call('dokuwiki.getPagelist', $params));

        idx_addPage('wiki:syntax'); //full text search depends on index
        $expected = array(
            array(
                'id' => 'wiki:syntax',
                'score' => 1,
                'rev' => filemtime($file2),
                'mtime' => filemtime($file2),
                'size' => filesize($file2),
                'snippet' => ' a footnote)) by using double parentheses.

===== <strong class="search_hit">Sectioning</strong> =====

You can use up to five different levels of',
                'title' => 'wiki:syntax'
            )
        );
        $params = array('Sectioning');
        $this->assertEquals($expected, $remoteApi->call('dokuwiki.search', $params));

        $timeexpect = time();
        $timeactual = $remoteApi->call('dokuwiki.getTime');
        $this->assertTrue(($timeexpect <= $timeactual) && ($timeactual <= $timeexpect + 1));

        $expected = array(
            'locked' => array('wiki:dokuwiki', 'wiki:syntax', 'nonexisting'),
            'lockfail' => array(),
            'unlocked' => array(),
            'unlockfail' => array(),
        );
        $params = array(
            array(
                'lock' => array('wiki:dokuwiki', 'wiki:syntax', 'nonexisting'),
                'unlock' => array()
            )
        );
        $this->assertEquals($expected, $remoteApi->call('dokuwiki.setLocks', $params));

        $expected = array(
            'locked' => array(),
            'lockfail' => array(),
            'unlocked' => array('wiki:dokuwiki', 'wiki:syntax', 'nonexisting'),
            'unlockfail' => array('nonexisting2'),
        );
        $params = array(
            array(
                'lock' => array(),
                'unlock' => array('wiki:dokuwiki', 'wiki:syntax', 'nonexisting', 'nonexisting2')
            )
        );
        $this->assertEquals($expected, $remoteApi->call('dokuwiki.setLocks', $params));

        global $conf;
        $this->assertEquals($conf['title'], $remoteApi->call('dokuwiki.getTitle'));

        $file3 = wikiFN('nice_page');
        $content = "====Title====\nText";
        $params = array(
            'nice_page',
            $content,
            array(
                'minor' => false,
                'sum' => 'Summary of nice text'
            )
        );
        $this->assertEquals(true, $remoteApi->call('wiki.putPage', $params));  //TODO check exceptions
        $this->assertEquals($content, rawWiki('nice_page'));

        $rev[1] = filemtime(wikiFN('nice_page')); //stored for later
        sleep(1); // wait for new revision ID

        $params = array('nice_page');
        $this->assertEquals($content, $remoteApi->call('wiki.getPage', $params));

        $morecontent = "\nOther text.";
        $secondcontent = $content . $morecontent;
        $params_append = array(
            'nice_page',
            $morecontent,
            array()
        );
        $this->assertEquals(true, $remoteApi->call('dokuwiki.appendPage', $params_append));
        $this->assertEquals($secondcontent, rawWiki('nice_page'));

        $params = array('nice_page', '');
        $this->assertEquals($secondcontent, $remoteApi->call('wiki.getPageVersion', $params));
        $params = array('nice_page', $rev[1]);
        $this->assertEquals($content, $remoteApi->call('wiki.getPageVersion', $params));
        $params = array('nice_page', 1234);
        $this->assertEquals('', $remoteApi->call('wiki.getPageVersion', $params), 'Not existing revision');
        $params = array('notexisting', 1234);
        $this->assertEquals('', $remoteApi->call('wiki.getPageVersion', $params), 'Not existing page');

        $html1 = "\n<h3 class=\"sectionedit1\" id=\"title\">Title</h3>\n<div class=\"level3\">\n\n<p>\nText\n";
        $html2 = "Other text.\n";
        $html3 = "</p>\n\n</div>\n";
        $params = array('nice_page');
        $this->assertEquals($html1 . $html2 . $html3, $remoteApi->call('wiki.getPageHTML', $params));

        $params = array('nice_page', '');
        $this->assertEquals($html1 . $html2 . $html3, $remoteApi->call('wiki.getPageHTMLVersion', $params));
        $params = array('nice_page', $rev[1]);
        $this->assertEquals($html1 . $html3, $remoteApi->call('wiki.getPageHTMLVersion', $params));
        $params = array('nice_page', 1234);
        $this->assertEquals('', $remoteApi->call('wiki.getPageHTMLVersion', $params));

        $expected = array(
            array(
                'id' => 'wiki:syntax',
                'perms' => 8,
                'size' => filesize($file2),
                'lastModified' => filemtime($file2)
            ),
            array(
                'id' => 'nice_page',
                'perms' => 8,
                'size' => filesize($file3),
                'lastModified' => filemtime($file3)
            )
        );
        $this->assertEquals($expected, $remoteApi->call('wiki.getAllPages')); //only indexed pages

        $params = array('wiki:syntax');
        $this->assertEquals(ft_backlinks('wiki:syntax'), $remoteApi->call('wiki.getBackLinks', $params));

        $expected = array(
            'name' => 'nice_page',
            'lastModified' => filemtime($file3),
            'author' => clientIP(),
            'version' => filemtime($file3)
        );
        $params = array('nice_page');
        $this->assertEquals($expected, $remoteApi->call('wiki.getPageInfo', $params));

        $expected = array(
            'name' => 'nice_page',
            'lastModified' => $rev[1],
            'author' => clientIP(),
            'version' => $rev[1]
        );
        $params = array('nice_page', $rev[1]);
        $this->assertEquals($expected, $remoteApi->call('wiki.getPageInfoVersion', $params));

        $rev[2] = filemtime(wikiFN('nice_page'));
        sleep(1); // wait for new revision ID
        $remoteApi->call('dokuwiki.appendPage', $params_append);
        $rev[3] = filemtime(wikiFN('nice_page'));
        sleep(1);
        $remoteApi->call('dokuwiki.appendPage', $params_append);
        $rev[4] = filemtime(wikiFN('nice_page'));
        sleep(1);
        $remoteApi->call('dokuwiki.appendPage', $params_append);
        $rev[5] = filemtime(wikiFN('nice_page'));
        sleep(1);
        $remoteApi->call('dokuwiki.appendPage', $params_append);
        $rev[6] = filemtime(wikiFN('nice_page'));

        $expected = array(
            array(
                'name' => 'nice_page',
                'lastModified' => $rev[6],
                'author' => '',
                'version' => $rev[6],
                'perms' => 8,
                'size' => 78
            )
        );
        $params = array(strtotime("-1 year"));
        $this->assertEquals($expected, $remoteApi->call('wiki.getRecentChanges', $params));

        $params = array('nice_page', 0);
        $versions = $remoteApi->call('wiki.getPageVersions', $params);
        $this->assertEquals($rev[6], $versions[0]['version']);
        $this->assertEquals($rev[5], $versions[1]['version']);
        $this->assertEquals($rev[1], $versions[5]['version']);
        $this->assertEquals(6, count($remoteApi->call('wiki.getPageVersions', $params)));

        $params = array('nice_page', 1);
        $versions = $remoteApi->call('wiki.getPageVersions', $params);
        $this->assertEquals($rev[5], $versions[0]['version']);
        $this->assertEquals($rev[4], $versions[1]['version']);
        $this->assertEquals(5, count($remoteApi->call('wiki.getPageVersions', $params)));

        $conf['recent'] = 3; //set number of page returned
        $params = array('nice_page', 1);
        $this->assertEquals(3, count($remoteApi->call('wiki.getPageVersions', $params)));

        $params = array('nice_page', $conf['recent']);
        $versions = $remoteApi->call('wiki.getPageVersions', $params);
        $this->assertEquals($rev[3], $versions[0]['version']); //skips current,1st old,2nd old
        $this->assertEquals(3, count($remoteApi->call('wiki.getPageVersions', $params)));

        $params = array('nice_page', 2 * $conf['recent']);
        $this->assertEquals(0, count($remoteApi->call('wiki.getPageVersions', $params)));

        //remove page
        $file3 = wikiFN('nice_page');
        $content = "";
        $params = array(
            'nice_page',
            $content,
            array(
                'minor' => false,
            )
        );
        $this->assertEquals(true, $remoteApi->call('wiki.putPage', $params));
        $this->assertFalse(file_exists($file3));

        $params = array('nice_page', 0);
        $this->assertEquals(2, count($remoteApi->call('wiki.getPageVersions', $params)));

        $params = array('nice_page', 1);
        $this->assertEquals(3, count($remoteApi->call('wiki.getPageVersions', $params)));

        $params = array('nice_page');
        $this->assertEquals(AUTH_UPLOAD, $remoteApi->call('wiki.aclCheck', $params));

        global $conf;
        global $AUTH_ACL, $USERINFO;
        $conf['useacl'] = 1;
        $_SERVER['REMOTE_USER'] = 'john';
        $USERINFO['grps'] = array('user');
        $AUTH_ACL = array(
            '*                  @ALL           0',
            '*                  @user          2', //edit
        );

        $params = array('nice_page');
        $this->assertEquals(AUTH_EDIT, $remoteApi->call('wiki.aclCheck', $params));

        $this->assertEquals(DOKU_API_VERSION, $remoteApi->call('dokuwiki.getXMLRPCAPIVersion'));

        $this->assertEquals(2, $remoteApi->call('wiki.getRPCVersionSupported'));
    }

    function test_core2() {
        $remoteApi = new RemoteApi();

        $localdoku = array(
            'type' => 'local',
            'page' => 'DokuWiki',
            'href' => DOKU_BASE . DOKU_SCRIPT . '?id=DokuWiki'
        );
        $expected = array(  //no local links
            $localdoku,
            array(
                'type' => 'extern',
                'page' => 'http://www.freelists.org',
                'href' => 'http://www.freelists.org'
            ),
            array(
                'type' => 'extern',
                'page' => 'https://tools.ietf.org/html/rfc1855',
                'href' => 'https://tools.ietf.org/html/rfc1855'
            ),
            array(
                'type' => 'extern',
                'page' => 'http://www.catb.org/~esr/faqs/smart-questions.html',
                'href' => 'http://www.catb.org/~esr/faqs/smart-questions.html'
            ),
            $localdoku,
            $localdoku
        );
        $params = array('mailinglist');
        $this->assertEquals($expected, $remoteApi->call('wiki.listLinks', $params));
    }

    function test_coreattachments() {
        global $conf;
        global $AUTH_ACL, $USERINFO;

        $remoteApi = new RemoteApi();

        $filecontent = io_readFile(mediaFN('wiki:dokuwiki-128.png'), false);
        $params = array('test:dokuwiki-128_2.png', $filecontent, array('ow' => false));
        $this->assertEquals('test:dokuwiki-128_2.png', $remoteApi->call('wiki.putAttachment', $params)); //prints a success div

        $params = array('test:dokuwiki-128_2.png');
        $this->assertEquals($filecontent, $remoteApi->call('wiki.getAttachment', $params));
        $rev = filemtime(mediaFN('test:dokuwiki-128_2.png'));

        $expected = array(
            'lastModified' => $rev,
            'size' => 27895,
        );
        $params = array('test:dokuwiki-128_2.png');
        $this->assertEquals($expected, $remoteApi->call('wiki.getAttachmentInfo', $params));

        $params = array(strtotime("-5 year"));
        $expected = array(
            array(
                'name' => 'test:dokuwiki-128_2.png',
                'lastModified' => $rev,
                'author' => '',
                'version' => $rev,
                'perms' => 8,
                'size' => 27895 //actual size, not size change
            )
        );
        $this->assertEquals($expected, $remoteApi->call('wiki.getRecentMediaChanges', $params));

        sleep(1);
        $conf['useacl'] = 1;
        $_SERVER['REMOTE_USER'] = 'john';
        $USERINFO['grps'] = array('user');
        $AUTH_ACL = array(
            '*                  @ALL           0',
            '*                  @user          16',
        );

        $params = array('test:dokuwiki-128_2.png');
        $this->assertEquals(0, $remoteApi->call('wiki.deleteAttachment', $params));

        $rev2 = filemtime($conf['media_changelog']);
        $expected = array(
            'lastModified' => $rev2,
            'size' => 0,
        );
        $params = array('test:dokuwiki-128_2.png');
        $this->assertEquals($expected, $remoteApi->call('wiki.getAttachmentInfo', $params));

        $expected = array(
            'lastModified' => 0,
            'size' => 0,
        );
        $params = array('test:nonexisting.png');
        $this->assertEquals($expected, $remoteApi->call('wiki.getAttachmentInfo', $params));

        $media1 = mediaFN('wiki:dokuwiki-128.png');
        $expected = array(
            array(
                'id' => 'wiki:dokuwiki-128.png',
                'file' => 'dokuwiki-128.png',
                'size' => filesize($media1),
                'mtime' => filemtime($media1),
                'writable' => 1,
                'isimg' => 1,
                'hash' => md5(io_readFile($media1, false)),
                'perms' => 16,
                'lastModified' => filemtime($media1)
            )
        );
        $params = array(
            'wiki:',
            array(
                'depth' => 0, // 0 for all
                'hash' => 1,
                'skipacl' => 1, // is ignored
                'showmsg' => true, //useless??
                'pattern' => '/128/' //filter
            )
        );
        $this->assertEquals($expected, $remoteApi->call('wiki.getAttachments', $params));
    }

}
