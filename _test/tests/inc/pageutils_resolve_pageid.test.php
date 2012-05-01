<?php

class init_resolve_pageid_test extends DokuWikiTest {


    function test1(){
        global $conf;

        // we test multiple cases here
        // format: $ns, $page, $output
        $tests   = array();

        // relative current in root
        $tests[] = array('','page','page');
        $tests[] = array('','.page','page');
        $tests[] = array('','.:page','page');

        // relative current in namespace
        $tests[] = array('lev1:lev2','page','lev1:lev2:page');
        $tests[] = array('lev1:lev2','.page','lev1:lev2:page');
        $tests[] = array('lev1:lev2','.:page','lev1:lev2:page');

        // relative upper in root
        $tests[] = array('','..page','page');
        $tests[] = array('','..:page','page');

        // relative upper in namespace
        $tests[] = array('lev1:lev2','..page','lev1:page');
        $tests[] = array('lev1:lev2','..:page','lev1:page');
        $tests[] = array('lev1:lev2','..:..:page','page');
        $tests[] = array('lev1:lev2','..:..:..:page','page');

        // strange and broken ones
        $tests[] = array('lev1:lev2','....:....:page','lev1:lev2:page');
        $tests[] = array('lev1:lev2','..:..:lev3:page','lev3:page');
        $tests[] = array('lev1:lev2','..:..:lev3:..:page','page');
        $tests[] = array('lev1:lev2','..:..:lev3:..:page:....:...','page');

        // now some tests with existing and none existing files
        $conf['start'] = 'start';

        $tests[] = array('','.:','start');
        $tests[] = array('foo','.:','foo:start');
        $tests[] = array('','foo:','foo:start');
        $tests[] = array('foo','foo:','foo:start');

        // empty $page
        global $ID;
        $ID = 'my:space';
        $tests[] = array('my', '', 'my:space');

        foreach($tests as $test){
            $page = $test[1];
            resolve_pageid($test[0],$page,$foo);

            $this->assertEquals($page,$test[2]);
        }
    }

    /**
     * Empty page on homepage should resolve to start page
     */
    function test_resolve_pageid_empty_homepage() {
        global $ID;
        $ID = '';
        global $conf;
        $conf['start'] = 'someverystrangestartname';

        $ns = '';
        $page = '';
        $exist = true;

        resolve_pageid($ns, $page, $exist);
        $this->assertEquals($page, $conf['start']);
   }

}
//Setup VIM: ex: et ts=4 :
