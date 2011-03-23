<?php

require_once DOKU_INC.'inc/common.php';

class common_pagetemplate_test extends UnitTestCase {

    function test_none(){
        global $conf;
        $conf['sepchar'] = '-';
        $data = array(
            'id' => 'page-id-long',
            'tpl' => '"@PAGE@" "@!PAGE@" "@!!PAGE@" "@!PAGE!@"',
        );
        $old = error_reporting(E_ALL & ~E_NOTICE);
        $this->assertEqual(parsePageTemplate($data), '"page id long" "Page id long" "Page Id Long" "PAGE ID LONG"');
        error_reporting($old);
    }
}
//Setup VIM: ex: et ts=4 :
