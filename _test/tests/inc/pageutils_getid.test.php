<?php

class init_getID_test extends EasyWikiTest {

    /**
     * id=0 case
     */
    function test_zero_id(){
        global $conf;
        $conf['basedir'] = '/';
        $conf['userewrite'] = 0;

        $_SERVER['SCRIPT_FILENAME'] = '/wiki.php';
        $_SERVER['REQUEST_URI'] = '/wiki.php?id=0&do=edit';
        $_REQUEST['id'] = '0';

        $this->assertSame('0', getID('id'));
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

        $this->assertEquals(getID('media'), 'myhdl-0.5dev1.tar.gz');
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
        $_SERVER['SCRIPT_FILENAME'] = '/var/www/easywiki/lib/exe/detail.php';
        $_SERVER['PHP_SELF'] = '/easywiki/lib/exe/detail.php/wiki/discussion/button-dw.png';
        $_SERVER['REQUEST_URI'] = '/easywiki/lib/exe/detail.php/wiki/discussion/button-dw.png?id=test&debug=1';
        $_SERVER['SCRIPT_NAME'] = '/easywiki/lib/exe/detail.php';
        $_SERVER['PATH_INFO'] = '/wiki/discussion/button-dw.png';
        $_SERVER['PATH_TRANSLATED'] = '/var/www/wiki/discussion/button-dw.png';

        $this->assertEquals(getID('media',true), 'wiki:discussion:button-dw.png');
        $this->assertEquals(getID('media',false), 'wiki/discussion/button-dw.png');
    }

    /**
     * getID with given id in url and userewrite=2, no basedir set, easywiki not in document root.
     */
    function test3() {
        global $conf;
        $conf['basedir'] = '';
        $conf['userewrite'] = '2';
        $conf['baseurl'] = '';
        $_SERVER['DOCUMENT_ROOT'] = '/var/www/';
        $_SERVER['SCRIPT_FILENAME'] = '/usr/share/easywiki/wiki.php';
        $_SERVER['SCRIPT_NAME'] = '/easywiki/wiki.php';
        $_SERVER['REQUEST_URI'] = '/easywiki/wiki.php/wiki:easywiki';
        $_SERVER['PATH_INFO'] = '/wiki:easywiki';
        $_SERVER['PATH_TRANSLATED'] = '/var/www/wiki:easywiki';
        $_SERVER['PHP_SELF'] = '/easywiki/wiki.php/wiki:easywiki';

        $this->assertEquals(getID(), 'wiki:easywiki');
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
        $_SERVER['SCRIPT_FILENAME'] = '/var/www/vhosts/example.com/htdocs/wiki.php';
        $_SERVER['SCRIPT_NAME'] = '/wiki.php';
        $_SERVER['REQUEST_URI'] = '/wiki.php/wiki/easywiki';
        $_SERVER['PATH_INFO'] = '/wiki/easywiki';
        $_SERVER['PATH_TRANSLATED'] = '/var/www/vhosts/example.com/htdocs/wiki.php';
        $_SERVER['PHP_SELF'] = '/wiki.php/wiki/easywiki';

        $this->assertEquals(getID(), 'wiki:easywiki');
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
        $_SERVER['SCRIPT_FILENAME'] = '/var/www/easywiki/wiki.php';
        $_SERVER['SCRIPT_NAME'] = '/easywiki/wiki.php';
        $_SERVER['REQUEST_URI'] = '/easywiki/wiki.php/?do=debug';
        $_SERVER['PATH_INFO'] = '/';
        $_SERVER['PATH_TRANSLATED'] = '/var/www/index.html';
        $_SERVER['PHP_SELF'] = '/easywiki/wiki.php/';

        $this->assertEquals(getID(), cleanID($conf['start']));
    }

}
//Setup VIM: ex: et ts=4 :
