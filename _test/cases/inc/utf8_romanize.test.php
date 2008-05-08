<?php
// use no mbstring help here
if(!defined('UTF8_NOMBSTRING')) define('UTF8_NOMBSTRING',1);
require_once DOKU_INC.'inc/utf8.php';

class utf8_substr_test extends UnitTestCase {

    /**
     * Check Japanese romanization
     *
     * @author Denis Scheither <amorphis@uni-bremen.de>
     */
    function test_japanese(){
        $tests = file(dirname(__FILE__).'/utf8_kanaromaji.txt');
        $line = 1;
        foreach($tests as $test){
            list($jap,$rom) = explode(';',trim($test));

            $chk = utf8_romanize($jap);
            #if($chk != $rom) echo "$jap\t->\t$chk\t!=\t$rom\t($line)\n";
            $this->assertEqual($chk,$rom);
            $line++;
        }
    }
}
//Setup VIM: ex: et ts=4 enc=utf-8 :
