<?php

namespace dokuwiki\plugin\struct\test;

use dokuwiki\plugin\struct\types\Url;

/**
 * Testing the Url Type
 *
 * @group plugin_struct
 * @group plugins
 */
class Type_Url_struct_test extends StructTest {

    /**
     * Provides failing validation data
     *
     * @return array
     */
    public function validateFailProvider() {
        return array(
            array('foo', '', '', ''),
            array('http', '', '', ''),
            array('http://', '', '', ''),
            array('foo', 'pre', '', ''),
            array('foo', '', 'post', ''),
            array('foo', 'pre', 'post', ''),

            array('http://', '', '', 'http')
        );
    }

    /**
     * Provides successful validation data
     *
     * @return array
     */
    public function validateSuccessProvider() {
        return array(
            array('http://www.example.com', '', '', ''),
            array('www.example.com', 'http://', '', ''),
            array('www.example.com', 'http://', 'bang', ''),
            array('http://www.example.com', '', 'bang', ''),

            array('foo', '', '', 'http'),
            array('http', '', '', 'http'),
            array('foo', 'pre', '', 'http'),
            array('foo', '', 'post', 'http'),
            array('foo', 'pre', 'post', 'http')
        );
    }

    /**
     * @expectedException \dokuwiki\plugin\struct\meta\ValidationException
     * @dataProvider validateFailProvider
     */
    public function test_validate_fail($value, $prefix, $postfix, $autoscheme) {
        $url = new Url(array('prefix' => $prefix, 'postfix' => $postfix, 'autoscheme' => $autoscheme));
        $url->validate($value);
    }

    /**
     * @dataProvider validateSuccessProvider
     */
    public function test_validate_success($value, $prefix, $postfix, $autoscheme) {
        $url = new Url(array('prefix' => $prefix, 'postfix' => $postfix, 'autoscheme' => $autoscheme));
        $url->validate($value);
        $this->assertTrue(true); // we simply check that no exceptions are thrown
    }
}
