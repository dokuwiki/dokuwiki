<?php

require_once DOKU_INC.'inc/IXR_Library.php';

/**
 * Class ixr_library_date_test
 */
class ixr_library_date_test extends DokuWikiTest {


    function test_parseIso(){
        // multiple tests
        $tests = array(
            // full datetime, different formats
            array('2010-08-17T09:23:14',  1282036994),
            array('20100817T09:23:14',    1282036994),
            array('2010-08-17 09:23:14',  1282036994),
            array('20100817 09:23:14',    1282036994),
            array('2010-08-17T09:23:14Z', 1282036994),
            array('20100817T09:23:14Z',   1282036994),

            // with timezone
            array('2010-08-17 09:23:14+0000',  1282036994),
            array('2010-08-17 09:23:14+00:00',  1282036994),
            array('2010-08-17 12:23:14+03:00',  1282036994),

            // no seconds
            array('2010-08-17T09:23',     1282036980),
            array('20100817T09:23',       1282036980),

            // no time
            array('2010-08-17',           1282003200),
            array(1282036980,             1282036980),
//            array('20100817',             1282003200), #this will NOT be parsed, but is assumed to be timestamp
        );

        foreach($tests as $test){
            $dt = new IXR_Date($test[0]);
            $this->assertEquals($test[1], $dt->getTimeStamp());
        }
    }

}
//Setup VIM: ex: et ts=4 :
