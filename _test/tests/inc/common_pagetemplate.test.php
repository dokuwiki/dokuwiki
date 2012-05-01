<?php

class common_pagetemplate_test extends DokuWikiTest {

    function test_none(){
        global $conf;
        $conf['sepchar'] = '-';
        $data = array(
            'id' => 'page-id-long',
            'tpl' => '"@PAGE@" "@!PAGE@" "@!!PAGE@" "@!PAGE!@"',
        );
        $this->assertEquals(parsePageTemplate($data), '"page id long" "Page id long" "Page Id Long" "PAGE ID LONG"');
    }
}
//Setup VIM: ex: et ts=4 :
