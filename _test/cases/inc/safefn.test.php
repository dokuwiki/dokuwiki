<?php
// use no mbstring help here
if(!defined('UTF8_NOMBSTRING')) define('UTF8_NOMBSTRING',1);
require_once DOKU_INC.'inc/utf8.php';
require_once DOKU_INC.'inc/SafeFN.class.php';

class safeFN_test extends UnitTestCase {


    function test1(){
        // we test multiple cases here - format: string, repl, additional, test
        $tests   = array();
        $tests[] = array('asciistring','asciistring');
        $tests[] = array('ascii-_/.string','ascii-_/.string');
        $tests[] = array('AName','%x%1a.ame');
        $tests[] = array('A Name','%x%0%1a.ame');
        $tests[] = array('Another...Name','%x.nother...%1a.ame');
        $tests[] = array('Aß∂ƒName','%x%5b%6oy%aa%1a.ame');
        $tests[] = array('A%ß-∂_.ƒName','%x%%5b.-%6oy._.%aa%1a.ame');
        $tests[] = array('A%%ß-∂_.ƒName','%x%%%5b.-%6oy._.%aa%1a.ame');
        $tests[] = array('데이터도 함께 복원됩니다. 강력한','%zf4%13dg%15ao%zhg%0%164o%yig%0%11at%138w%zk9%zag%zb8..%0%xyt%10cl%164c');
        $tests[] = array('совместимая','%td%ta%sy%t8%t1%td%te%t4%t8%sw%tr');
        $tests[] = array('нехватка_файлового_пространства_на_сервере_p0-squid.some.domain.1270211897.txt.gz','%t9%t1%th%sy%sw%te%t6%sw._%tg%sw%t5%t7%ta%sy%ta%sz%ta._%tb%tc%ta%td%te%tc%sw%t9%td%te%sy%sw._%t9%sw._%td%t1%tc%sy%t1%tc%t1._p0-squid.some.domain.1270211897.txt.gz');

        foreach($tests as $test){
            list($utf8,$safe) = $test;
            $this->assertEqual(SafeFN::encode($utf8),$safe);
            $this->assertEqual(SafeFN::decode($safe),$utf8);
        }
    }

}
//Setup VIM: ex: et ts=4 enc=utf-8 :
