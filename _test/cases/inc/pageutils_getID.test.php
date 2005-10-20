<?php

require_once DOKU_INC.'inc/pageutils.php';

class init_getID_test extends UnitTestCase {

    /**
     * fetch media files with basedir and urlrewrite=2
     * 
     * data provided by Jan Decaluwe <jan@jandecaluwe.com>
     */
    function test1(){
        global $conf;
        $conf['basedir'] = '//';
        $conf['urlrewrite'] = 2;
        $conf['deaccent'] = 0; // the default (1) gives me strange exceptions


        $_SERVER['SCRIPT_FILENAME'] = '/lib/exe/fetch.php';
        $_SERVER['REQUEST_URI'] = '/lib/exe/fetch.php/myhdl-0.5dev1.tar.gz?id=snapshots&cache=cache';

	$this->assertEqual(getID($param='not_id'), 'myhdl-0.5dev1.tar.gz');
    }


}

