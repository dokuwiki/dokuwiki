<?php

class wikifn_test extends DokuWikiTest {


    function test_cache_cleaning_cleanToUnclean(){
        $this->assertEquals(wikiFN('wiki:',null,false), TestUtils::w2u(DOKU_TMP_DATA.'pages/wiki/.txt'));
        $this->assertEquals(wikiFN('wiki:',null,true), TestUtils::w2u(DOKU_TMP_DATA.'pages/wiki.txt'));
    }

    function test_cache_cleaning_uncleanToClean(){
        $this->assertEquals(wikiFN('wiki:',null,true), TestUtils::w2u(DOKU_TMP_DATA.'pages/wiki.txt'));
        $this->assertEquals(wikiFN('wiki:',null,false), TestUtils::w2u(DOKU_TMP_DATA.'pages/wiki/.txt'));
    }

}
//Setup VIM: ex: et ts=4 :
