<?php

require_once DOKU_INC.'inc/init.php';
require_once DOKU_INC.'inc/common.php';

class common_pagetemplate_test extends UnitTestCase {

    function test_none(){
        global $conf;
        $conf['sepchar'] = '-';
        $data = array(
            'id' => 'page-id-long',
            'tpl' => '"@PAGE@" "@!PAGE@" "@!!PAGE@" "@!PAGE!@"',
        );
        $this->assertEqual(parsePageTemplate($data), '"page id long" "Page id long" "Page Id Long" "PAGE ID LONG"');
    }
}
//Setup VIM: ex: et ts=4 :
