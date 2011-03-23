<?php
require_once DOKU_INC.'inc/utf8.php';
require_once DOKU_INC.'inc/pageutils.php';

global $conf;
if (!isset($conf['datadir'])) $conf['datadir'] = $conf['savedir'].'/pages';

class init_resolve_pageid_test extends UnitTestCase {


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
        $tests[] = array('foo','playground:','playground:playground');


        foreach($tests as $test){
            $page = $test[1];
            resolve_pageid($test[0],$page,$foo);

            $this->assertEqual($page,$test[2]);
        }
    }

}
//Setup VIM: ex: et ts=4 :
