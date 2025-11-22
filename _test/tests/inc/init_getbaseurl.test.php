<?php

class init_getBaseURL_test extends EasyWikiTest {

    /**
     * Apache, mod_php, subdirectory
     * 
     * data provided by Andreas Gohr <andi@splitbrain.org>
     */
    function test1(){
        global $conf;
        $conf['basedir'] = '';
        $conf['baseurl'] = '';
        $conf['canonical'] = 0;

        $_SERVER['DOCUMENT_ROOT']   = '/var/www/';
        $_SERVER['HTTP_HOST']       = 'xerxes.my.home';
        $_SERVER['SCRIPT_FILENAME'] = '/var/www/easywiki/wiki.php';
        $_SERVER['REQUEST_URI']     = '/easywiki/wiki.php?do=debug';
        $_SERVER['SCRIPT_NAME']     = '/easywiki/wiki.php';
        $_SERVER['PATH_INFO']       = null;
        $_SERVER['PATH_TRANSLATED'] = '/var/www/easywiki/wiki.php';
        $_SERVER['PHP_SELF']        = '/easywiki/wiki.php';

        $this->assertEquals(getBaseURL(),'/easywiki/');
    }

    /**
     * Apache, CGI, mod_userdir, subdirectory
     *
     * data provided by Hilko Bengen <bengen@hilluzination.de>
     */
    function test2(){
        global $conf;
        $conf['basedir'] = '';
        $conf['baseurl'] = '';
        $conf['canonical'] = 0;

        $_SERVER['DOCUMENT_ROOT']   = '/var/www/localhost';
        $_SERVER['HTTP_HOST']       = 'localhost';
        $_SERVER['SCRIPT_FILENAME'] = '/usr/lib/cgi-bin/php4';
        $_SERVER['REQUEST_URI']     = '/~bengen/easywiki/wiki.php?do=debug';
        $_SERVER['SCRIPT_NAME']     = '/cgi-bin/php4';
        $_SERVER['PATH_INFO']       = '/~bengen/easywiki/wiki.php';
        $_SERVER['PATH_TRANSLATED'] = '/home/bengen/public_html/easywiki/wiki.php';
        $_SERVER['PHP_SELF']        = '/~bengen/easywiki/wiki.php';

        $this->assertEquals(getBaseURL(),'/~bengen/easywiki/');
    }

    /**
     * Apache, FastCGI, mod_userdir, subdirectory
     *
     * data provided by Hilko Bengen <bengen@hilluzination.de>
     */
    function test3(){
        global $conf;
        $conf['basedir'] = '';
        $conf['baseurl'] = '';
        $conf['canonical'] = 0;

        $_SERVER['DOCUMENT_ROOT']   = '/var/www/localhost';
        $_SERVER['HTTP_HOST']       = 'localhost';
        $_SERVER['SCRIPT_FILENAME'] = '/var/run/php-fastcgi/fcgi-bin/bengen/php4';
        $_SERVER['REQUEST_URI']     = '/~bengen/easywiki/wiki.php?do=debug';
        $_SERVER['SCRIPT_NAME']     = '/fcgi-bin/php4-bengen';
        $_SERVER['PATH_INFO']       = '/~bengen/easywiki/wiki.php';
        $_SERVER['PATH_TRANSLATED'] = '/home/bengen/public_html/easywiki/wiki.php';
        $_SERVER['PHP_SELF']        = '/~bengen/easywiki/wiki.php';

        $this->assertEquals(getBaseURL(),'/~bengen/easywiki/');
    }

    /**
     * Apache, mod_php, mod_userdir, subdirectory
     *
     * data provided by Hilko Bengen <bengen@hilluzination.de>
     */
    function test4(){
        global $conf;
        $conf['basedir'] = '';
        $conf['baseurl'] = '';
        $conf['canonical'] = 0;

        $_SERVER['DOCUMENT_ROOT']   = '/var/www/localhost';
        $_SERVER['HTTP_HOST']       = 'localhost';
        $_SERVER['SCRIPT_FILENAME'] = '/home/bengen/public_html/easywiki/wiki.php';
        $_SERVER['REQUEST_URI']     = '/~bengen/easywiki/wiki.php?do=debug';
        $_SERVER['SCRIPT_NAME']     = '/~bengen/easywiki/wiki.php';
        $_SERVER['PATH_INFO']       = null;
        $_SERVER['PATH_TRANSLATED'] = '/home/bengen/public_html/easywiki/wiki.php';
        $_SERVER['PHP_SELF']        = '/~bengen/easywiki/wiki.php';

        $this->assertEquals(getBaseURL(),'/~bengen/easywiki/');
    }

    /**
     * IIS
     *
     * data provided by David Mach <david.mach@centrum.cz>
     */
    function test5(){
        global $conf;
        $conf['basedir'] = '';
        $conf['baseurl'] = '';
        $conf['canonical'] = 0;

        $_SERVER['DOCUMENT_ROOT']   = null;
        $_SERVER['HTTP_HOST']       = 'intranet';
        $_SERVER['SCRIPT_FILENAME'] = null;
        $_SERVER['REQUEST_URI']     = null; 
        $_SERVER['SCRIPT_NAME']     = '/wiki/wiki.php';
        $_SERVER['PATH_INFO']       = '/wiki/wiki.php';
        $_SERVER['PATH_TRANSLATED'] = 'C:\\Inetpub\\wwwroot\\wiki\\wiki.php';
        $_SERVER['PHP_SELF']        = '/wiki/wiki.php';
    
        $this->assertEquals(getBaseURL(),'/wiki/');
    }

    /**
     * Apache 2, mod_php, real URL rewriting, useslash (bug #292)
     *
     * data provided by Ted <bugsX2904@elcsplace.com>
     */
    function test6(){
        global $conf;
        $conf['basedir'] = '';
        $conf['baseurl'] = '';
        $conf['canonical'] = 0;

        $_SERVER['DOCUMENT_ROOT']   = '/home/websites/wiki/htdocs';
        $_SERVER['HTTP_HOST']       = 'wiki.linuxwan.net';
        $_SERVER['SCRIPT_FILENAME'] = '/home/websites/wiki/htdocs/wiki.php';
        $_SERVER['REQUEST_URI']     = '/wiki/syntax?do=debug';
        $_SERVER['SCRIPT_NAME']     = '/wiki/syntax';
        $_SERVER['PATH_INFO']       = null;
        $_SERVER['PATH_TRANSLATED'] = null;
        $_SERVER['PHP_SELF']        = '/wiki/syntax';
    
        $this->assertEquals(getBaseURL(),'/');
    }

    /**
     * lighttpd, fastcgi
     *
     * data provided by Andreas Gohr <andi@splitbrain.org>
     */
    function test7(){
        global $conf;
        $conf['basedir'] = '';
        $conf['baseurl'] = '';
        $conf['canonical'] = 0;

        $_SERVER['DOCUMENT_ROOT']   = '/var/www/';
        $_SERVER['HTTP_HOST']       = 'localhost';
        $_SERVER['SCRIPT_FILENAME'] = '/var/www/easywiki/wiki.php';
        $_SERVER['REQUEST_URI']     = '/easywiki/wiki.php?do=debug';
        $_SERVER['SCRIPT_NAME']     = '/easywiki/wiki.php';
        $_SERVER['PATH_INFO']       = '';
        $_SERVER['PATH_TRANSLATED'] = null;
        $_SERVER['PHP_SELF']        = '';
   
        $this->assertEquals(getBaseURL(),'/easywiki/');
    }

    /**
     * Apache, mod_php, Pseudo URL rewrite, useslash
     *
     * data provided by Andreas Gohr <andi@splitbrain.org>
     */
    function test8(){
        global $conf;
        $conf['basedir'] = '';
        $conf['baseurl'] = '';
        $conf['canonical'] = 0;

        $_SERVER['DOCUMENT_ROOT']   = '/var/www/';
        $_SERVER['HTTP_HOST']       = 'xerxes.my.home';
        $_SERVER['SCRIPT_FILENAME'] = '/var/www/easywiki/wiki.php';
        $_SERVER['REQUEST_URI']     = '/easywiki/wiki.php/wiki/syntax?do=debug';
        $_SERVER['SCRIPT_NAME']     = '/easywiki/wiki.php';
        $_SERVER['PATH_INFO']       = '/wiki/syntax';
        $_SERVER['PATH_TRANSLATED'] = '/var/www/wiki/syntax';
        $_SERVER['PHP_SELF']        = '/easywiki/wiki.php/wiki/syntax';

        $this->assertEquals(getBaseURL(),'/easywiki/');
    }

    /**
     * Apache, mod_php, real URL rewrite, useslash
     *
     * data provided by Andreas Gohr <andi@splitbrain.org>
     */
    function test9(){
        global $conf;
        $conf['basedir'] = '';
        $conf['baseurl'] = '';
        $conf['canonical'] = 0;

        $_SERVER['DOCUMENT_ROOT']   = '/var/www/';
        $_SERVER['HTTP_HOST']       = 'xerxes.my.home';
        $_SERVER['SCRIPT_FILENAME'] = '/var/www/easywiki/wiki.php';
        $_SERVER['REQUEST_URI']     = '/easywiki/wiki/syntax?do=debug';
        $_SERVER['SCRIPT_NAME']     = '/easywiki/wiki.php';
        $_SERVER['PATH_INFO']       = null;
        $_SERVER['PATH_TRANSLATED'] = '/var/www/easywiki/wiki.php';
        $_SERVER['PHP_SELF']        = '/easywiki/wiki.php';

        $this->assertEquals(getBaseURL(),'/easywiki/');
    }

    /**
     * Possible user settings of $conf['baseurl'] & absolute baseURL required
     *
     * data provided by Andreas Gohr <andi@splitbrain.org>
     */
    function test10(){
        // values for $conf['baseurl'] and expected results
        $tests = array(
          'http://www.mysite.com' => 'http://www.mysite.com/easywiki/',
          'http://www.mysite.com/' => 'http://www.mysite.com/easywiki/',
          'http://www.mysite.com/path/to/wiki' => 'http://www.mysite.com/path/to/wiki/easywiki/',
          'http://www.mysite.com/path/to/wiki/' => 'http://www.mysite.com/path/to/wiki/easywiki/',
         );

        global $conf;
        $conf['basedir'] = '';
        $conf['baseurl'] = '';

        $_SERVER['DOCUMENT_ROOT']   = '/var/www/';
        $_SERVER['HTTP_HOST']       = 'xerxes.my.home';
        $_SERVER['SCRIPT_FILENAME'] = '/var/www/easywiki/wiki.php';
        $_SERVER['REQUEST_URI']     = '/easywiki/wiki/syntax?do=debug';
        $_SERVER['SCRIPT_NAME']     = '/easywiki/wiki.php';
        $_SERVER['PATH_INFO']       = null;
        $_SERVER['PATH_TRANSLATED'] = '/var/www/easywiki/wiki.php';
        $_SERVER['PHP_SELF']        = '/easywiki/wiki.php';

        foreach ($tests as $test => $correct_result) {
          $conf['baseurl'] = $test;
          $this->assertEquals(getBaseURL(true),$correct_result);
        }
    }
    /**
     * Possible user settings of $conf['baseurl'] & absolute baseURL required
     *
     * data provided by Andreas Gohr <andi@splitbrain.org>
     */
    function test11(){
        // values for $conf['baseurl'] and expected results
        $tests = array(
          'http://www.mysite.com' => 'http://www.mysite.com/easywiki/',
          'http://www.mysite.com/' => 'http://www.mysite.com/easywiki/',
          'http://www.mysite.com/path/to/wiki' => 'http://www.mysite.com/path/to/wiki/easywiki/',
          'http://www.mysite.com/path/to/wiki/' => 'http://www.mysite.com/path/to/wiki/easywiki/',
         );

        global $conf;
        $conf['basedir'] = '/easywiki';
        $conf['baseurl'] = '';

        $_SERVER['DOCUMENT_ROOT']   = '/var/www/';
        $_SERVER['HTTP_HOST']       = 'xerxes.my.home';
        $_SERVER['SCRIPT_FILENAME'] = '/var/www/easywiki/wiki.php';
        $_SERVER['REQUEST_URI']     = '/easywiki/wiki/syntax?do=debug';
        $_SERVER['SCRIPT_NAME']     = '/easywiki/wiki.php';
        $_SERVER['PATH_INFO']       = null;
        $_SERVER['PATH_TRANSLATED'] = '/var/www/easywiki/wiki.php';
        $_SERVER['PHP_SELF']        = '/easywiki/wiki.php';

        foreach ($tests as $test => $correct_result) {
          $conf['baseurl'] = $test;
          $this->assertEquals(getBaseURL(true),$correct_result);
        }
    }

    /**
     * Absolute URL with IPv6 domain name.
     * lighttpd, fastcgi
     *
     * data provided by Michael Hamann <michael@content-space.de>
     */
    function test12() {
        global $conf;
        $conf['basedir'] = '';
        $conf['baseurl'] = '';
        $conf['canonical'] = 0;

        $_SERVER['DOCUMENT_ROOT'] = '/srv/http/';
        $_SERVER['HTTP_HOST'] = '[fd00::6592:39ed:a2ed:2c78]';
        $_SERVER['SCRIPT_FILENAME'] = '/srv/http/~michitux/easywiki/wiki.php';
        $_SERVER['REQUEST_URI'] = '/~michitux/easywiki/wiki.php?do=debug';
        $_SERVER['SCRIPT_NAME'] = '/~michitux/easywiki/wiki.php';
        $_SERVER['PATH_INFO'] = null;
        $_SERVER['PATH_TRANSLATED'] = null;
        $_SERVER['PHP_SELF'] = '/~michitux/easywiki/wiki.php';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SERVER_NAME'] = '[fd00';
        $this->assertEquals(getBaseURL(true), 'http://[fd00::6592:39ed:a2ed:2c78]/~michitux/easywiki/');
    }
}

//Setup VIM: ex: et ts=2 :
