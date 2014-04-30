<?php
// use no mbstring help here
if(!defined('UTF8_NOMBSTRING')) define('UTF8_NOMBSTRING',1);

/**
 * @group slow
 */
class utf8_romanize_test extends DokuWikiTest {

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
            $this->assertEquals($rom,$chk,"$jap\t->\t$chk\t!=\t$rom\t($line)");
            $line++;
        }
    }

    /**
     * Test romanization of character that would usually be deaccented in a different
     * way FS#1117
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function test_deaccented(){
        $this->assertEquals("a A a A a o O",utf8_romanize("å Å ä Ä ä ö Ö"));
    }
}
//Setup VIM: ex: et ts=4 :
