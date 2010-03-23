<?php

require_once DOKU_INC.'inc/indexer.php';

class indexer_idx_indexlengths_test extends UnitTestCase {

    /**
     * Test the function with an array of one value
     */
    function test_oneWord(){
        global $conf;
        $filter[8] = array('dokuwiki');
        // one word should return the index
        $ref[] = 8;
        sort($ref);
        $result = idx_indexLengths(&$filter);
        sort($result);
        $this->assertIdentical($result, $ref);
    }

    /**
     * Test the function with an array of values
     */
    function test_moreWords() {
        global $conf;
        $filter = array( 4 => array('test'), 8 => array('dokuwiki'), 7 => array('powered'));
        // more words should return the indexes
        $ref = array(4, 7, 8);
        sort($ref);
        $result = idx_indexLengths(&$filter);
        sort($result);
        $this->assertIdentical($result, $ref);
    }

    /**
     * Test a minimal value in case of wildcard search
     */
    function test_minValue() {
        global $conf;
        $filter = 5;
        // construction of the list of the index to compare
        $dir = @opendir($conf['indexdir']);
        $ref = array();
        while (($f = readdir($dir)) !== false) {
            if (substr($f,0,1) == 'i' && substr($f,-4) == '.idx'){
                $i = substr($f,1,-4);
                if (is_numeric($i) && $i >= $filter)
                $ref[] = (int)$i;
            }
        }
        closedir($dir);
        sort($ref);
        $result = idx_indexLengths(&$filter);
        sort($result);
        $this->assertIdentical($result, $ref);
    }
}

class indexer_idx_indexlengths_time extends UnitTestCase {

    /**
     * Test the time improvments of the new function
     * Time reference for 10000 call oneWords: 4,6s
     * It's 90% faster
     */
    function test_oneWord(){
        global $conf;
        $filter[8] = array('dokuwiki');
        $start = microtime(true);
        for ($i = 0; $i < 10000; $i++) {
            $result = idx_indexLengths(&$filter);
        }
        $end = microtime(true);
        $time = $end - $start;
        $timeref = 4.6*0.10; // actual execution time of 4,6s for 10000 calls
        echo "1) 10% ref : $timeref -> $time \n";
        $this->assertTrue($time < $timeref);
    }

    /**
     * Test the time improvments of the new function
     * Time reference for 10000 call moreWords: 4,6s
     * It's 90% faster
     */
    function test_moreWords() {
        global $conf;
        $filter = array( 4 => array('test'), 8 => array('dokuwiki'), 7 => array('powered'));
        // more words should return the indexes
        $start = microtime(true);
        for ($i = 0; $i < 10000; $i++) {
            $result = idx_indexLengths(&$filter);
        }
        $end = microtime(true);
        $time = $end - $start;
        $timeref = 4.6*0.10; // actual execution time of 4,6s for 10000 calls
        echo "2) 10% ref : $timeref -> $time \n";
        $this->assertTrue($time < $timeref);
    }

    /**
     * Test the time improvments of the new function
     * Time reference for 10000 call on minValue: 4,9s
     * Sould be at least 65% faster
     * Test fail with no cache
     */
    function test_minValue() {
        global $conf;
        $filter = 5;
        $start = microtime(true);
        for ($i = 0; $i < 10000; $i++) {
            $result = idx_indexLengths(&$filter);
        }
        $end = microtime(true);
        $time = $end - $start;
        $timeref = 4.9 * 0.35; // actual execution time of 4,9s for 10000 calls
        echo "3) 35% ref : $timeref -> $time \n";
        $this->assertTrue($time < $timeref);
    }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
