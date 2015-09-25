<?php

class common_blank_test extends DokuWikiTest {

    private $nope;

    function test_blank() {
        $tests = array(
            // these are not blank
            array('string', false),
            array(1, false),
            array(1.0, false),
            array(0xff, false),
            array(array('something'), false),

            // these aren't either!
            array('0', false),
            array(' ', false),
            array('0.0', false),
            array(0, false),
            array(0.0, false),
            array(0x00, false),
            array(true, false),

            // but these are
            array('', true),
            array(array(), true),
            array(null, true),
            array(false, true),
            array("\0", true)
        );

        foreach($tests as $test) {
            $this->assertEquals($test[1], blank($test[0]), "using " . var_export($test[0], true));
        }
    }

    function test_trim() {
        $whitespace = " \t\r\n";
        $this->assertFalse(blank($whitespace), "using default \$trim value");
        $this->assertFalse(blank($whitespace, false), "using \$trim = false");
        $this->assertTrue(blank($whitespace, true), "using \$trim = true");
    }

    function test_undefined() {
        $undef = array();
        $this->assertTrue(blank($var), "using undefined/unitialised variable");
        $this->assertTrue(blank($undef['nope']), "using undefined array index");
        $this->assertTrue(blank($this->nope), "using unset object property");
    }

}
