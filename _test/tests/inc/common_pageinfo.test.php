<?php
/**
 * Unit Test for inc/common.php - pageinfo()
 *
 * @author Christopher Smith <chris@jalakai.co.uk>
 */
class common_pageinfo_test extends DokuWikiTest {

    public function setup() : void {
        parent::setup();

        global $USERINFO;
        $USERINFO = [
           'pass' => '179ad45c6ce2cb97cf1029e212046e81',
           'name' => 'Arthur Dent',
           'mail' => 'arthur@example.com',
           'grps' => ['admin', 'user'],
        ];
        $_SERVER['REMOTE_USER'] = 'testuser';
        $_SERVER['REMOTE_ADDR'] = '1.2.3.4';
    }

    protected function get_expected_pageinfo() {
        global $USERINFO;
        $info = [
          'isadmin' => true,
          'ismanager' => true,
          'userinfo' => $USERINFO,
          'perm' => 255,
          'namespace' => false,
          'ismobile' => false,
          'client' => 'testuser',
        ];
        $info['rev'] = null;
        $info['subscribed'] = false;
        $info['locked'] = false;
        $info['exists'] = false;
        $info['writable'] = true;
        $info['editable'] = true;
        $info['lastmod'] = false;
        $info['currentrev'] = false;
        $info['meta'] = [];
        $info['ip'] = null;
        $info['user'] = null;
        $info['sum'] = null;
        $info['editor'] = null;

        return $info;
    }

    /**
     *  check info keys and values for a non-existent page & admin user
     */
    public function test_basic_nonexistentpage() {
        global $ID,$conf;
        $ID = 'wiki:start';

        $info = $this->get_expected_pageinfo();
        $info['id'] = 'wiki:start';
        $info['namespace'] = 'wiki';
        $info['filepath'] = $conf['datadir'].'/wiki/start.txt';

        $this->assertEquals($info, pageinfo());
    }

    /**
     *  check info keys and values for a existing page & admin user
     */
    public function test_basic_existingpage() {
        global $ID,$conf;
        $ID = 'wiki:syntax';
        $filename = $conf['datadir'].'/wiki/syntax.txt';
        $rev = filemtime($filename);

        // pageinfo() adds the meta['last_change'] entry on first access; capture the
        // result before reading the expected metadata so it reflects that entry
        $result = pageinfo();

        $info = $this->get_expected_pageinfo();
        $info['id'] = 'wiki:syntax';
        $info['namespace'] = 'wiki';
        $info['filepath'] = $filename;
        $info['exists'] = true;
        $info['lastmod'] = $rev;
        $info['currentrev'] = $rev;
        $info['meta'] = p_get_metadata($ID);
        // set from revinfo, $pagelog->getRevisionInfo($info['lastmod'])
        $info = array_merge($info, [
            'ip' => '127.0.0.1',
            'user' => '',
            'sum' => 'created - external edit',
        ]);
        $info['editor'] = '127.0.0.1';

        $this->assertEquals($info, $result);
    }

    /**
     *  check info keys and values for anonymous user
     */
    public function test_anonymoususer() {
        global $ID,$conf,$REV;

        unset($_SERVER['REMOTE_USER']);
        global $USERINFO; $USERINFO = [];

        $ID = 'wiki:syntax';
        $filename = $conf['datadir'].'/wiki/syntax.txt';
        $rev = filemtime($filename);

        // pageinfo() adds the meta['last_change'] entry on first access; capture the
        // result before building the expectation so p_get_metadata() sees that entry
        $result = pageinfo();

        $info = $this->get_expected_pageinfo();
        $info['id'] = 'wiki:syntax';
        $info['namespace'] = 'wiki';
        $info['filepath'] = $filename;
        $info['exists'] = true;
        $info['lastmod'] = $rev;
        $info['currentrev'] = $rev;
        $info['meta'] = p_get_metadata($ID);
        // set from revinfo, $pagelog->getRevisionInfo($info['lastmod'])
        $info = array_merge($info, [
            'ip' => '127.0.0.1',
            'user' => '',
            'sum' => 'created - external edit',
        ]);
        $info['editor'] = '127.0.0.1';

        // anonymous user
        $info = array_merge($info, [
          'isadmin' => false,
          'ismanager' => false,
          'perm' => 8,
          'client' => '1.2.3.4',
        ]);
        unset($info['userinfo']);

        $this->assertEquals($info, $result);
    }

    /**
     *  check info keys and values with $REV
     *  (also see $RANGE tests)
     */
    public function test_rev() {
        global $ID,$conf,$REV;

        $ID = 'wiki:syntax';
        $filename = $conf['datadir'].'/wiki/syntax.txt';
        $rev = filemtime($filename);
        $REV = $rev - 100;
        $ext = '.txt';
        if ($conf['compression']) {
            //compression in $info['filepath'] determined by wikiFN depends also on if the page exist
            $ext .= "." . $conf['compression']; //.gz or .bz2
        }

        $info = $this->get_expected_pageinfo();
        $info['id'] = 'wiki:syntax';
        $info['namespace'] = 'wiki';
        $info['meta'] = p_get_metadata($ID);
        $info['rev'] = $REV;
        $info['currentrev'] = $rev;
        $info['filepath'] = str_replace('pages','attic',substr($filename,0,-3).$REV.$ext);

        $this->assertEquals($info, pageinfo());
        $this->assertEquals($rev-100, $REV);
    }

    /**
     *  check info keys and values with $RANGE
     */
    public function test_range() {
        global $ID,$conf,$REV,$RANGE;

        $ID = 'wiki:syntax';
        $filename = $conf['datadir'].'/wiki/syntax.txt';
        $rev = filemtime($filename);
        $range = '1000-2000';

        $info = $this->get_expected_pageinfo();
        $info['id'] = 'wiki:syntax';
        $info['namespace'] = 'wiki';
        $info['exists'] = true;
        $info['lastmod'] = $rev;
        $info['currentrev'] = $rev;
        $info['filepath'] = $filename;
        // set from revinfo, $pagelog->getRevisionInfo($info['lastmod'])
        $info = array_merge($info, [
            'ip' => '127.0.0.1',
            'user' => '',
            'sum' => 'created - external edit',
        ]);
        $info['editor'] = '127.0.0.1';

        // check $RANGE without $REV
        // expected result $RANGE unchanged
        $RANGE = $range;

        // pageinfo() adds the meta['last_change'] entry on first access; capture the
        // result before reading the expected metadata so it reflects that entry
        $result = pageinfo();
        $info['meta'] = p_get_metadata($ID);

        $this->assertEquals($info, $result);
        $this->assertFalse(isset($REV));
        $this->assertEquals($range, $RANGE);

        // check $RANGE with $REV = current
        // expected result: $RANGE unchanged, $REV cleared
        $REV = $rev;
        $info['rev'] = '';

        $this->assertEquals($info, pageinfo());
        $this->assertEquals('',$REV);
        $this->assertEquals($range, $RANGE);

        // check with a real $REV
        // expected result: $REV and $RANGE are cleared
        $REV = $rev - 100;

        $this->assertEquals($info, pageinfo());
        $this->assertEquals('', $REV);
        $this->assertEquals('', $RANGE);
    }

    /**
     *  test editor entry and external edit
     */
    public function test_editor_and_externaledits() {
        global $ID,$conf;
        // use a dedicated page here: this test mutates the changelog and file mtime
        // (adds a changelog entry and touch()es the file), so it must not run against
        // wiki:syntax which the other tests rely on being pristine
        $ID = 'wiki:dokuwiki';
        $filename = $conf['datadir'].'/wiki/dokuwiki.txt';
        $rev = filemtime($filename);

        $info = $this->get_expected_pageinfo();
        $info['id'] = 'wiki:dokuwiki';
        $info['namespace'] = 'wiki';
        $info['filepath'] = $filename;
        $info['exists'] = true;
        $info['lastmod'] = $rev;
        $info['currentrev'] = $rev;
        $info['meta'] = p_get_metadata($ID);  // need $INFO set correctly for updateMetadata()

        global $INFO;
        $INFO = $info;

        // add an editor for the current version of $ID using the PageFile API
        $pageFile = new \dokuwiki\File\PageFile($ID);
        $logEntry = $pageFile->changelog->addLogEntry([
            'date'       => $rev,
            'ip'         => $_SERVER['REMOTE_ADDR'],
            'type'       => DOKU_CHANGE_TYPE_EDIT,
            'id'         => $ID,
            'user'       => $_SERVER['REMOTE_USER'],
            'sum'        => '',
            'extra'      => '',
            'sizechange' => '',
        ]);
        $pageFile->updateMetadata($logEntry);

        $info['meta'] = p_get_metadata($ID);
        $info['ip'] = $_SERVER['REMOTE_ADDR'];
        $info['user'] = $_SERVER['REMOTE_USER'];
        $info['sum'] = '';
        $info['editor'] = $info['user'];

        // with an editor ...
        $this->assertEquals($info, pageinfo());

        // clear the meta['last_change'] value, pageinfo should restore it
        p_set_metadata($ID, ['last_change' => false]);

        $this->assertEquals($info, pageinfo());
        $this->assertEquals($info['meta']['last_change'], p_get_metadata($ID,'last_change'));

        // fake an external edit, pageinfo should clear the last change from meta data
        // and not return any editor data
        $now = time() + 10;
        touch($filename, $now);

        $info['lastmod'] = $now;
        $info['currentrev'] = $now;
        $info['meta']['last_change'] = false;
        $info['ip'] = null;
        $info['user'] = null;
        $info['sum'] = null;
        $info['editor'] = null;

        $this->assertEquals($info, pageinfo());
        $this->assertEquals($info['meta'], p_get_metadata($ID));   // check metadata has been updated correctly
    }

    /**
     *  check draft
     */
    public function test_draft() {
        global $ID,$conf;
        $ID = 'wiki:syntax';
        $filename = $conf['datadir'].'/wiki/syntax.txt';
        $rev = filemtime($filename);

        $info = $this->get_expected_pageinfo();
        $info['id'] = 'wiki:syntax';
        $info['namespace'] = 'wiki';
        $info['filepath'] = $filename;
        $info['exists'] = true;
        $info['lastmod'] = $rev;
        $info['currentrev'] = $rev;
        // set from revinfo, $pagelog->getRevisionInfo($info['lastmod'])
        $info = array_merge($info, [
            'ip' => '127.0.0.1',
            'user' => '',
            'sum' => 'created - external edit',
        ]);
        $info['editor'] = '127.0.0.1';

        // setup a draft, make it more recent than the current page
        // - pageinfo should recognise it and keep it

        $draft = getCacheName($info['client']."\n".$ID,'.draft');
        touch($draft, $rev + 10);

        $info['draft'] = $draft;

        // pageinfo() adds the meta['last_change'] entry on first access; capture the
        // result before reading the expected metadata so it reflects that entry
        $result = pageinfo();
        $info['meta'] = p_get_metadata($ID);

        $this->assertEquals($info, $result);
        $this->assertFileExists($draft);

        // make the draft older than the current page
        // - pageinfo should remove it and not return the 'draft' key
        touch($draft,$rev - 10);
        unset($info['draft']);

        $this->assertEquals($info, pageinfo());
        $this->assertFalse(file_exists($draft));
    }

    /**
     *  check ismobile
     */
    public function test_ismobile() {
        global $ID,$conf;
        $ID = 'wiki:start';

        $info = $this->get_expected_pageinfo();
        $info['id'] = 'wiki:start';
        $info['namespace'] = 'wiki';
        $info['filepath'] = $conf['datadir'].'/wiki/start.txt';

        // overkill, ripped from clientismobile() as we aren't testing detection - but forcing it
        $_SERVER['HTTP_X_WAP_PROFILE'] = 'a fake url';
        $_SERVER['HTTP_ACCEPT'] .= ';wap';
        $_SERVER['HTTP_USER_AGENT'] = 'blackberry,symbian,hand,mobi,phone';

        $info['ismobile'] = clientismobile();

        $this->assertTrue(clientismobile());     // ensure THIS test fails if clientismobile() returns false
        $this->assertEquals($info, pageinfo());  // it would be a test failure not a pageinfo failure.
    }
}
