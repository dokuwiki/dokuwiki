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


//Setup VIM: ex: et ts=4 :
