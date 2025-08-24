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

            $chk = \dokuwiki\Utf8\Clean::romanize($jap);
            $this->assertEquals($rom,$chk,"$jap\t->\t$chk\t!=\t$rom\t($line)");
            $line++;
        }
    }

    /**
     * Check Korean romanization
     *
     * @author Denis Scheither <amorphis@uni-bremen.de>
     */
    function test_korean(){
        $tests = file(dirname(__FILE__).'/utf8_koreanromanize.txt');
        $line = 1;
        foreach($tests as $test){
            list($kor,$rom) = explode(';',trim($test));

            $chk = \dokuwiki\Utf8\Clean::romanize($kor);
            $this->assertEquals($rom,$chk,"$kor\t->\t$chk\t!=\t$rom\t($line)");
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
        $this->assertEquals("a A a A a o O",\dokuwiki\Utf8\Clean::romanize("å Å ä Ä ä ö Ö"));
    }

    /**
     * Greeklish romanization
     */
    function test_greeklish(){
        $this->assertEquals('kalimera pos eiste',\dokuwiki\Utf8\Clean::romanize('Καλημέρα πώς είστε'));
    }

}

