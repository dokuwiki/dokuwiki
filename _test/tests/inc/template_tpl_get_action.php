<?php

class template_tpl_get_action_test extends DokuWikiTest {

    public function setUp() : void {
        parent::setUp();
        global $ID;
        $ID = 'start'; // run all tests on the start page

    }

    public function test_edit_edit() {
        global $ACT;
        global $INFO;
        global $REV;

        $ACT = 'show';
        $REV = '';
        $INFO['writable'] = true;
        $INFO['exists'] = true;
        $INFO['draft'] = '';

        $expect = array(
            'accesskey' => 'e',
            'type' => 'edit',
            'id' => 'start',
            'method' => 'post',
            'params' => array(
                'do' => 'edit',
                'rev' => '',
            ),
            'nofollow' => true,
            'replacement' => '',
        );
        $this->assertEquals($expect, tpl_get_action('edit'));
    }

    public function test_edit_edit_rev() {
        global $ACT;
        global $INFO;
        global $REV;

        $ACT = 'show';
        $REV = '1234';
        $INFO['writable'] = true;
        $INFO['exists'] = true;
        $INFO['draft'] = '';

        $expect = array(
            'accesskey' => 'e',
            'type' => 'edit',
            'id' => 'start',
            'method' => 'post',
            'params' => array(
                'do' => 'edit',
                'rev' => '1234',
            ),
            'nofollow' => true,
            'replacement' => '',
        );
        $this->assertEquals($expect, tpl_get_action('edit'));
    }

    public function test_edit_create() {
        global $ACT;
        global $INFO;
        global $REV;

        $ACT = 'show';
        $REV = '';
        $INFO['writable'] = true;
        $INFO['exists'] = false;
        $INFO['draft'] = '';

        $expect = array(
            'accesskey' => 'e',
            'type' => 'create',
            'id' => 'start',
            'method' => 'post',
            'params' => array(
                'do' => 'edit',
                'rev' => '',
            ),
            'nofollow' => true,
            'replacement' => '',
        );
        $this->assertEquals($expect, tpl_get_action('edit'));
    }

    public function test_edit_draft() {
        global $ACT;
        global $INFO;
        global $REV;

        $ACT = 'show';
        $REV = '';
        $INFO['writable'] = true;
        $INFO['exists'] = true;
        $INFO['draft'] = 'foobar';

        $expect = array(
            'accesskey' => 'e',
            'type' => 'draft',
            'id' => 'start',
            'method' => 'post',
            'params' => array(
                'do' => 'draft',
            ),
            'nofollow' => true,
            'replacement' => '',
        );
        $this->assertEquals($expect, tpl_get_action('edit'));
    }

    public function test_edit_show() {
        global $ACT;
        global $INFO;
        global $REV;

        $ACT = 'edit';
        $REV = '';
        $INFO['writable'] = true;
        $INFO['exists'] = true;
        $INFO['draft'] = '';

        $expect = array(
            'accesskey' => 'v',
            'type' => 'show',
            'id' => 'start',
            'method' => 'get',
            'params' => array(
                'do' => '',
            ),
            'nofollow' => true,
            'replacement' => '',
        );
        $this->assertEquals($expect, tpl_get_action('edit'));
    }

    public function test_revisions() {
        $expect = array(
            'accesskey' => 'o',
            'type' => 'revs',
            'id' => 'start',
            'method' => 'get',
            'params' => array(
                'do' => 'revisions',
            ),
            'nofollow' => true,
            'replacement' => '',
        );

        $this->assertEquals($expect, tpl_get_action('history'));
        $this->assertEquals($expect, tpl_get_action('revisions'));
    }

    public function test_recent() {
        $expect = array(
            'accesskey' => 'r',
            'type' => 'recent',
            'id' => 'start',
            'method' => 'get',
            'params' => array(
                'do' => 'recent',
            ),
            'nofollow' => true,
            'replacement' => '',

        );
        $this->assertEquals($expect, tpl_get_action('recent'));
    }

    public function test_login() {
        $expect = array(
            'accesskey' => null,
            'type' => 'login',
            'id' => 'start',
            'method' => 'get',
            'params' => array(
                'do' => 'login',
                'sectok' => '',
            ),
            'nofollow' => true,
            'replacement' => '',
        );
        $this->assertEquals($expect, tpl_get_action('login'));

        $_SERVER['REMOTE_USER'] = 'someone'; // logged in user

        $expect = array(
            'accesskey' => null,
            'type' => 'logout',
            'id' => 'start',
            'method' => 'get',
            'params' => array(
                'do' => 'logout',
                'sectok' => getSecurityToken(),
            ),
            'nofollow' => true,
            'replacement' => '',
        );
        $this->assertEquals($expect, tpl_get_action('login'));
    }

    public function test_profile() {
        $expect = false;
        $this->assertEquals($expect, tpl_get_action('profile'));

        $_SERVER['REMOTE_USER'] = 'someone'; // logged in user

        $expect = array(
            'accesskey' => null,
            'type' => 'profile',
            'id' => 'start',
            'method' => 'get',
            'params' => array(
                'do' => 'profile',
            ),
            'nofollow' => true,
            'replacement' => '',
        );
        $this->assertEquals($expect, tpl_get_action('profile'));
    }

    public function test_index() {
        $expect = array(
            'accesskey' => 'x',
            'type' => 'index',
            'id' => 'start',
            'method' => 'get',
            'params' => array(
                'do' => 'index',
            ),
            'nofollow' => false,
            'replacement' => '',
        );
        $this->assertEquals($expect, tpl_get_action('index'));

        global $ID;
        $ID = 'wiki:syntax';  // change to different page

        $expect = array(
            'accesskey' => 'x',
            'type' => 'index',
            'id' => 'wiki:syntax',
            'method' => 'get',
            'params' => array(
                'do' => 'index',
            ),
            'nofollow' => true,
            'replacement' => '',
        );
        $this->assertEquals($expect, tpl_get_action('index'));
    }

    public function test_admin() {
        $expect = false;
        $this->assertEquals($expect, tpl_get_action('admin'));

        // logged in super user
        global $INFO;
        $_SERVER['REMOTE_USER'] = 'testuser';
        $INFO['ismanager'] = true;

        $expect = array(
            'accesskey' => null,
            'type' => 'admin',
            'id' => 'start',
            'method' => 'get',
            'params' => array(
                'do' => 'admin',
            ),
            'nofollow' => true,
            'replacement' => '',
        );
        $this->assertEquals($expect, tpl_get_action('admin'));
    }

    public function test_top() {
        $expect = array(
            'accesskey' => 't',
            'type' => 'top',
            'id' => '#dokuwiki__top',
            'method' => 'get',
            'params' => array(
                'do' => '',
            ),
            'nofollow' => true,
            'replacement' => '',
        );
        $this->assertEquals($expect, tpl_get_action('top'));
    }

    public function test_back() {
        $expect = false;
        $this->assertEquals($expect, tpl_get_action('back'));

        global $ID;
        $ID = 'wiki:syntax';

        $expect = array(
            'accesskey' => 'b',
            'type' => 'back',
            'id' => 'wiki:start',
            'method' => 'get',
            'params' => array(
                'do' => '',
            ),
            'nofollow' => true,
            'replacement' => '',
        );
        $this->assertEquals($expect, tpl_get_action('back'));
    }

    public function test_backlink() {
        $expect = array(
            'accesskey' => null,
            'type' => 'backlink',
            'id' => 'start',
            'method' => 'get',
            'params' => array(
                'do' => 'backlink',
            ),
            'nofollow' => true,
            'replacement' => '',
        );
        $this->assertEquals($expect, tpl_get_action('backlink'));
    }

    public function test_subscribe() {
        $expect = false;
        $this->assertEquals($expect, tpl_get_action('subscribe'));
        $this->assertEquals($expect, tpl_get_action('subscription'));

        $_SERVER['REMOTE_USER'] = 'someone'; // logged in user

        $expect = false;
        $this->assertEquals($expect, tpl_get_action('subscribe'));
        $this->assertEquals($expect, tpl_get_action('subscription'));

        // enable subscriptions
        global $conf;
        $conf['subscribers'] = true;

        $expect = array(
            'accesskey' => null,
            'type' => 'subscribe',
            'id' => 'start',
            'method' => 'get',
            'params' => array(
                'do' => 'subscribe',
            ),
            'nofollow' => true,
            'replacement' => '',
        );
        $this->assertEquals($expect, tpl_get_action('subscribe'));
        $this->assertEquals($expect, tpl_get_action('subscription'));
    }

    public function test_register() {
        $expect = array(
            'accesskey' => null,
            'type' => 'register',
            'id' => 'start',
            'method' => 'get',
            'params' => array(
                'do' => 'register',
            ),
            'nofollow' => true,
            'replacement' => '',
        );
        $this->assertEquals($expect, tpl_get_action('register'));

        $_SERVER['REMOTE_USER'] = 'somebody'; // logged in user

        $expect = false;
        $this->assertEquals($expect, tpl_get_action('register'));
    }

    public function test_resendpwd() {
        $expect = array(
            'accesskey' => null,
            'type' => 'resendpwd',
            'id' => 'start',
            'method' => 'get',
            'params' => array(
                'do' => 'resendpwd',
            ),
            'nofollow' => true,
            'replacement' => '',
        );
        $this->assertEquals($expect, tpl_get_action('resendpwd'));

        $_SERVER['REMOTE_USER'] = 'somebody'; // logged in user

        $expect = false;
        $this->assertEquals($expect, tpl_get_action('resendpwd'));
    }

    public function test_revert() {
        $expect = false;
        $this->assertEquals($expect, tpl_get_action('revert'));

        global $REV;
        global $INFO;
        $REV = '1234';
        $INFO['writable'] = true;
        $INFO['ismanager'] = true;

        $expect = array(
            'accesskey' => null,
            'type' => 'revert',
            'id' => 'start',
            'method' => 'get', // FIXME should this be post?
            'params' => array(
                'do' => 'revert',
                'rev' => '1234',
                'sectok' => '' // FIXME is this correct?
            ),
            'nofollow' => true,
            'replacement' => '',
        );
        $this->assertEquals($expect, tpl_get_action('revert'));
    }

    public function test_media() {
        global $ID;
        $ID = 'wiki:syntax';

        $expect = array(
            'accesskey' => null,
            'type' => 'media',
            'id' => 'wiki:syntax',
            'method' => 'get',
            'params' => array(
                'do' => 'media',
                'ns' => 'wiki'
            ),
            'nofollow' => true,
            'replacement' => '',
        );
        $this->assertEquals($expect, tpl_get_action('media'));
    }

    public function test_mediaManager() {
        global $IMG;
        $IMG = 'wiki:dokuwiki.png';

        $expect = array(
            'accesskey' => null,
            'type' => 'mediaManager',
            'id' => 'start',
            'method' => 'get',
            'params' => array(
                'do' => 'media',
                'ns' => 'wiki',
                'image' => 'wiki:dokuwiki.png'
            ),
            'nofollow' => true,
            'replacement' => '',
        );
        $this->assertEquals($expect, tpl_get_action('mediaManager'));
    }

    public function test_img_backto() {
        $expect = array(
            'accesskey' => 'b',
            'type' => 'img_backto',
            'id' => 'start',
            'method' => 'get',
            'params' => array(),
            'nofollow' => true,
            'replacement' => 'start',
        );
        $this->assertEquals($expect, tpl_get_action('img_backto'));
    }

    public function test_unknown() {
        $expect = '[unknown %s type]';
        $this->assertEquals($expect, tpl_get_action('unknown'));
    }

}
