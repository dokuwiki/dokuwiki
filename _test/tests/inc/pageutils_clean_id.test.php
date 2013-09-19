<?php

class init_clean_id_test extends DokuWikiTest {

    function teardown() {
        global $cache_cleanid;
        $cache_cleanid = array();
    }

    function test_default(){
        // we test multiple cases here
        // format: $id, $ascii, $correct_output
        $tests   = array();

        // set dokuwiki defaults
        global $conf;
        $conf['sepchar'] = '_';
        $conf['deaccent'] = 1;

        $tests[] = array('page',false,'page');
        $tests[] = array('pa_ge',false,'pa_ge');
        $tests[] = array('pa%ge',false,'pa_ge');
        $tests[] = array('pa#ge',false,'pa_ge');
        $tests[] = array('pàge',false,'page');
        $tests[] = array('pagĖ',false,'page');
        $tests[] = array('pa$%^*#ge',false,'pa_ge');
        $tests[] = array('*page*',false,'page');
        $tests[] = array('ښ',false,'ښ');
        $tests[] = array('päge',false,'paege');
        $tests[] = array('foo bar',false,'foo_bar');
        $tests[] = array('PÄGÖ',false,'paegoe');
        $tests[] = array('Faß','false','fass');
        $tests[] = array('ښ侧化并곦  β',false,'ښ侧化并곦_β');
        $tests[] = array('page:page',false,'page:page');
        $tests[] = array('page;page',false,'page:page');
        $tests[] = array('page:page 1.2',false,'page:page_1.2');

        $tests[] = array('page._#!','false','page');
        $tests[] = array('._#!page','false','page');
        $tests[] = array('page._#!page','false','page._page');
        $tests[] = array('ns._#!:page','false','ns:page');
        $tests[] = array('ns:._#!page','false','ns:page');
        $tests[] = array('ns._#!ns:page','false','ns._ns:page');
        $tests[] = array('ns_:page',false,'ns:page');
        $tests[] = array('page...page','false','page...page');
        $tests[] = array('page---page','false','page---page');
        $tests[] = array('page___page','false','page_page');
        $tests[] = array('page_-.page','false','page_-.page');
        $tests[] = array(':page',false,'page');
        $tests[] = array(':ns:page',false,'ns:page');
        $tests[] = array('page:',false,'page');
        $tests[] = array('ns:page:',false,'ns:page');

        $conf['useslash'] = 0;
        $tests[] = array('page/page',false,'page_page');

        foreach($tests as $test){
            $this->assertEquals(cleanID($test[0],$test[1]),$test[2]);
        }

        $conf['useslash'] = 1;
        $tests = array();
        $tests[] = array('page/page',false,'page:page');

        $this->teardown();

        foreach($tests as $test){
            $this->assertEquals(cleanID($test[0],$test[1]),$test[2]);
        }
    }

    function test_sepchar(){
        // we test multiple cases here
        // format: $id, $ascii, $correct_output
        $tests   = array();

        global $conf;
        $conf['sepchar'] = '-';
        $conf['deaccent'] = 1;

        $tests[] = array('pa-ge',false,'pa-ge');
        $tests[] = array('pa%ge',false,'pa-ge');

        foreach($tests as $test){
            $this->assertEquals(cleanID($test[0],$test[1]),$test[2]);
        }
    }

    function test_deaccent_keep(){
        // we test multiple cases here
        // format: $id, $ascii, $correct_output
        $tests   = array();

        global $conf;
        $conf['sepchar'] = '_';
        $conf['deaccent'] = 0;

        $tests[] = array('pàge',false,'pàge');
        $tests[] = array('pagĖ',false,'pagė');
        $tests[] = array('pagĒēĔĕĖėĘęĚě',false,'pagēēĕĕėėęęěě');
        $tests[] = array('ښ',false,'ښ');
        $tests[] = array('ښ侧化并곦ঝഈβ',false,'ښ侧化并곦ঝഈβ');

        foreach($tests as $test){
            $this->assertEquals(cleanID($test[0],$test[1]),$test[2]);
        }
    }

    function test_deaccent_romanize(){
        // we test multiple cases here
        // format: $id, $ascii, $correct_output
        $tests   = array();

        global $conf;
        $conf['sepchar'] = '_';
        $conf['deaccent'] = 2;

        $tests[] = array('pàge',false,'page');
        $tests[] = array('pagĖ',false,'page');
        $tests[] = array('pagĒēĔĕĖėĘęĚě',false,'pageeeeeeeeee');
        $tests[] = array('ښ',false,'ښ');
        $tests[] = array('ښ侧化并곦ঝഈβ',false,'ښ侧化并곦ঝഈβ');

        foreach($tests as $test){
            $this->assertEquals(cleanID($test[0],$test[1]),$test[2]);
        }
    }

    function test_deaccent_ascii(){
        // we test multiple cases here
        // format: $id, $ascii, $correct_output
        $tests   = array();

        global $conf;
        $conf['sepchar'] = '_';
        $conf['deaccent'] = 0;

        $tests[] = array('pàge',true,'page');
        $tests[] = array('pagĖ',true,'page');
        $tests[] = array('pagĒēĔĕĖėĘęĚě',true,'pageeeeeeeeee');
        $tests[] = array('ښ',true,'');
        $tests[] = array('ښ侧化并곦ঝഈβ',true,'');

        foreach($tests as $test){
            $this->assertEquals(cleanID($test[0],$test[1]),$test[2]);
        }

        $conf['deaccent'] = 1;

        foreach($tests as $test){
            $this->assertEquals(cleanID($test[0],$test[1]),$test[2]);
        }

        $conf['deaccent'] = 2;

        foreach($tests as $test){
            $this->assertEquals(cleanID($test[0],$test[1]),$test[2]);
        }
    }

}
//Setup VIM: ex: et ts=4 :
