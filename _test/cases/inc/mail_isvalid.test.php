<?php
// use no mbstring help here
require_once DOKU_INC.'inc/mail.php';

class mail_isvalid extends UnitTestCase {


    function test1(){
        // we test multiple cases here - format: string, repl, additional, test
        $tests   = array();
        $tests[] = array('bugs@php.net',true);
        $tests[] = array('~someone@somewhere.com',true);
        $tests[] = array('no+body.here@somewhere.com.au',true);
        $tests[] = array("rfc2822+allthesechars_#*!'`/-={}are.legal@somewhere.com.au",true);
        $tests[] = array('bugs@php.net1',false);
        $tests[] = array('.bugs@php.net1',false);
        $tests[] = array('bu..gs@php.net',false);
        $tests[] = array('bugs@php..net',false);
        $tests[] = array('bugs@.php.net',false);
        $tests[] = array('bugs@php.net.',false);
        $tests[] = array('bu(g)s@php.net1',false);
        $tests[] = array('bu[g]s@php.net1',false);
        $tests[] = array('somebody@somewhere.museum',true);
        $tests[] = array('somebody@somewhere.travel',true);

        foreach($tests as $test){
            $this->assertEqual($test[0].'('.mail_isvalid($test[0]).')',$test[0].'('.(int)$test[1].')');
        }
    }

}
//Setup VIM: ex: et ts=4 enc=utf-8 :
