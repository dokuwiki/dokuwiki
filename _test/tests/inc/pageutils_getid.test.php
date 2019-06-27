<?php

class init_getID_test extends DokuWikiTest {

    /**
     * id=0 case
     */
    function test_zero_id(){
        global $conf;
        $conf['basedir'] = '/';
        $conf['userewrite'] = 0;

        $_SERVER['SCRIPT_FILENAME'] = '/doku.php';
        $_SERVER['REQUEST_URI'] = '/doku.php?id=0&do=edit';
        $_REQUEST['id'] = '0';

        $this->assertSame(getID('id'), '0');
    }

    /**
     * fetch media files with basedir and urlrewrite=2
     *
     * data provided by Jan Decaluwe <jan@jandecaluwe.com>
     */
    function test1(){
        global $conf;
        $conf['basedir'] = '//';
        $conf['userewrite'] = 2;
        $conf['deaccent'] = 0; // the default (1) gives me strange exceptions


        $_SERVER['SCRIPT_FILENAME'] = '/lib/exe/fetch.php';
        $_SERVER['REQUEST_URI'] = '/lib/exe/fetch.php/myhdl-0.5dev1.tar.gz?id=snapshots&cache=cache';

        $this->assertEquals('myhdl-0.5dev1.tar.gz', getID('media'));
    }


    /**
     * getID with internal mediafile, urlrewrite=2, no basedir set, apache, mod_php
     */
    function test2(){
        global $conf;
        $conf['basedir'] = '';
        $conf['userewrite'] = '2';
        $conf['baseurl'] = '';
        $conf['useslash'] = '1';
        $_SERVER['DOCUMENT_ROOT'] = '/var/www/';
        $_SERVER['HTTP_HOST'] = 'xerxes.my.home';
        $_SERVER['SCRIPT_FILENAME'] = '/var/www/dokuwiki/lib/exe/detail.php';
        $_SERVER['PHP_SELF'] = '/dokuwiki/lib/exe/detail.php/wiki/discussion/button-dw.png';
        $_SERVER['REQUEST_URI'] = '/dokuwiki/lib/exe/detail.php/wiki/discussion/button-dw.png?id=test&debug=1';
        $_SERVER['SCRIPT_NAME'] = '/dokuwiki/lib/exe/detail.php';
        $_SERVER['PATH_INFO'] = '/wiki/discussion/button-dw.png';
        $_SERVER['PATH_TRANSLATED'] = '/var/www/wiki/discussion/button-dw.png';

        $this->assertEquals('wiki:discussion:button-dw.png', getID('media',true));
        $this->assertEquals('wiki/discussion/button-dw.png', getID('media',false));
    }

    /**
     * getID with given id in url and userewrite=2, no basedir set, dokuwiki not in document root.
     */
    function test3() {
        global $conf;
        $conf['basedir'] = '';
        $conf['userewrite'] = '2';
        $conf['baseurl'] = '';
        $_SERVER['DOCUMENT_ROOT'] = '/var/www/';
        $_SERVER['SCRIPT_FILENAME'] = '/usr/share/dokuwiki/doku.php';
        $_SERVER['SCRIPT_NAME'] = '/dokuwiki/doku.php';
        $_SERVER['REQUEST_URI'] = '/dokuwiki/doku.php/wiki:dokuwiki';
        $_SERVER['PATH_INFO'] = '/wiki:dokuwiki';
        $_SERVER['PATH_TRANSLATED'] = '/var/www/wiki:dokuwiki';
        $_SERVER['PHP_SELF'] = '/dokuwiki/doku.php/wiki:dokuwiki';

        $this->assertEquals('wiki:dokuwiki', getID());
    }

    /**
     * getID with given id in url and userewrite=2, no basedir set, Apache and CGI.
     */
    function test4() {
        global $conf;
        $conf['basedir'] = '';
        $conf['userewrite'] = '2';
        $conf['baseurl'] = '';
        $conf['useslash'] = '1';

        $_SERVER['DOCUMENT_ROOT'] = '/var/www/vhosts/example.com/htdocs';
        $_SERVER['SCRIPT_FILENAME'] = '/var/www/vhosts/example.com/htdocs/doku.php';
        $_SERVER['SCRIPT_NAME'] = '/doku.php';
        $_SERVER['REQUEST_URI'] = '/doku.php/wiki/dokuwiki';
        $_SERVER['PATH_INFO'] = '/wiki/dokuwiki';
        $_SERVER['PATH_TRANSLATED'] = '/var/www/vhosts/example.com/htdocs/doku.php';
        $_SERVER['PHP_SELF'] = '/doku.php/wiki/dokuwiki';

        $this->assertEquals('wiki:dokuwiki', getID());
    }

    /**
     * getID with given id / in url and userewrite=2, no basedir set, Apache and CGI.
     */
    function test5() {
        global $conf;
        $conf['basedir'] = '';
        $conf['userewrite'] = '2';
        $conf['baseurl'] = '';
        $_SERVER['DOCUMENT_ROOT'] = '/var/www/';
        $_SERVER['SCRIPT_FILENAME'] = '/var/www/dokuwiki/doku.php';
        $_SERVER['SCRIPT_NAME'] = '/dokuwiki/doku.php';
        $_SERVER['REQUEST_URI'] = '/dokuwiki/doku.php/?do=debug';
        $_SERVER['PATH_INFO'] = '/';
        $_SERVER['PATH_TRANSLATED'] = '/var/www/index.html';
        $_SERVER['PHP_SELF'] = '/dokuwiki/doku.php/';

        $this->assertEquals(cleanID($conf['start']), getID());
    }

    /**
     * getID with given id in url and userewrite=2, basedir set, Apache and CGI.
     */
    function test_default_ns(){
        global $conf;
        $cleanStart = cleanID($conf['start']);
        $conf['basedir'] = '/dw/';
        $conf['userewrite'] = '2';
        $conf['baseurl'] = '';
        $conf['useslash'] = '1';

        // root test
        $this->_test_default_ns('/', $cleanStart);
        $this->_test_default_ns('', $cleanStart);

        // for "foo:bar:"
        // order foo:bar:$conf['start'] -> foo:bar:bar -> foo:bar
        saveWikiText('foo:bar', 'test1', '');
        $this->_test_default_ns('/foo/bar/', 'foo:bar');

        saveWikiText('foo:bar:bar', 'test2', '');
        $this->_test_default_ns('/foo/bar/', 'foo:bar:bar');

        saveWikiText('foo:bar:'.$cleanStart, 'test3', '');
        $this->_test_default_ns('/foo/bar/', 'foo:bar:'.$cleanStart);
    }

    function _test_default_ns($path, $expected){
        global $conf;
        $_SERVER['DOCUMENT_ROOT'] = '/var/www/vhosts/example.com/htdocs';
        $_SERVER['SCRIPT_FILENAME'] = '/var/www/vhosts/example.com/htdocs/dw/doku.php';
        $_SERVER['SCRIPT_NAME'] = '/dw/doku.php';
        $_SERVER['REQUEST_URI'] = '/dw/doku.php'.$path;
        $_SERVER['PATH_INFO'] = '/dw'.$path;
        $_SERVER['PATH_TRANSLATED'] = '/var/www/vhosts/example.com/htdocs/dw/doku.php';
        $_SERVER['PHP_SELF'] = '/dw/doku.php'.$path;

        $this->assertEquals($expected, getID());
    }

}
//Setup VIM: ex: et ts=4 :
