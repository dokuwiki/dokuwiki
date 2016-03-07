<?php

namespace plugin\struct\test;

use plugin\struct\types\Url;

spl_autoload_register(array('action_plugin_struct_autoloader', 'autoloader'));

/**
 * Testing the Integer Type
 *
 * @group plugin_struct
 * @group plugins
 */
class Type_Url_struct_test extends \DokuWikiTest {

    protected $pluginsEnabled = array('struct', 'sqlite');

    /**
     * Provides failing validation data
     *
     * @return array
     */
    public function validateFailProvider() {
        return array(
            array('foo', '', ''),
            array('http', '', ''),
            array('http://', '', ''),
            array('foo', 'pre', ''),
            array('foo', '', 'post'),
            array('foo', 'pre', 'post')
        );
    }

    /**
     * Provides successful validation data
     *
     * @return array
     */
    public function validateSuccessProvider() {
        return array(
            array('http://www.example.com', '', ''),
            array('www.example.com', 'http://', ''),
            array('www.example.com', 'http://', 'bang'),
            array('http://www.example.com', '', 'bang'),
        );
    }

    /**
     * @expectedException \plugin\struct\meta\ValidationException
     * @dataProvider validateFailProvider
     */
    public function test_validate_fail($value, $prefix, $postfix) {
        $url = new Url(array('prefix' => $prefix, 'postfix' => $postfix));
        $url->validate($value);
    }

    /**
     * @dataProvider validateSuccessProvider
     */
    public function test_validate_success($value, $prefix, $postfix) {
        $url = new Url(array('prefix' => $prefix, 'postfix' => $postfix));
        $url->validate($value);
        $this->assertTrue(true); // we simply check that no exceptions are thrown
    }
}
