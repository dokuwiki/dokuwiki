<?php
// use no mbstring help here
if(!defined('UTF8_NOMBSTRING')) define('UTF8_NOMBSTRING',1);

class utf8_stripspecials extends DokuWikiTest {


    function test1(){
        // we test multiple cases here - format: string, repl, additional, test
        $tests   = array();
        $tests[] = array('asciistring','','','asciistring');
        $tests[] = array('asciistring','','\._\-:','asciistring');
        $tests[] = array('ascii.string','','\._\-:','asciistring');
        $tests[] = array('ascii.string',' ','\._\-:','ascii string');
        $tests[] = array('2.1.14',' ','\._\-:','2 1 14');
        $tests[] = array('ascii.string','','\._\-:\*','asciistring');
        $tests[] = array('ascii.string',' ','\._\-:\*','ascii string');
        $tests[] = array('2.1.14',' ','\._\-:\*','2 1 14');
        $tests[] = array('string with nbsps','_','\*','string_with_nbsps');

        foreach($tests as $test){
            $this->assertEquals(\dokuwiki\Utf8\Clean::stripspecials($test[0],$test[1],$test[2]),$test[3]);
        }
    }

}
//Setup VIM: ex: et ts=4 :
