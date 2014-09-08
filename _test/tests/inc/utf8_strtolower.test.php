<?php
// use no mbstring help here
if(!defined('UTF8_NOMBSTRING')) define('UTF8_NOMBSTRING',1);

class utf8_strtolower_test extends DokuWikiTest {

    function test_givens(){
        $data = array(
            'Αρχιτεκτονική Μελέτη' => 'αρχιτεκτονική μελέτη', // FS#2173
        );

        foreach($data as $input => $expected) {
            $this->assertEquals($expected, utf8_strtolower($input));
        }

        // just make sure our data was correct
        if(function_exists('mb_strtolower')) {
            foreach($data as $input => $expected) {
                $this->assertEquals($expected, mb_strtolower($input, 'utf-8'));
            }
        }
    }
}