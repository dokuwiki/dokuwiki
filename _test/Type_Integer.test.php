<?php

namespace plugin\struct\test;

use plugin\struct\types\Integer;

spl_autoload_register(array('action_plugin_struct_autoloader', 'autoloader'));

/**
 * Testing the Integer Type
 *
 * @group plugin_struct
 * @group plugins
 */
class Type_Integer_struct_test extends StructTest {

    /**
     * Provides failing validation data
     *
     * @return array
     */
    public function validateFailProvider() {
        return array(
            array('foo', '', ''),
            array('foo222', '', ''),
            array('-5', '0', ''),
            array('5', '', '0'),
            array('500', '100', '200'),
            array('50', '100', '200'),
        );
    }

    /**
     * Provides successful validation data
     *
     * @return array
     */
    public function validateSuccessProvider() {
        return array(
            array('0', '', ''),
            array('-5', '', ''),
            array('5', '', ''),
            array('5', '0', ''),
            array('-5', '', '0'),
            array('150', '100', '200'),
        );
    }

    /**
     * @expectedException \plugin\struct\meta\ValidationException
     * @dataProvider validateFailProvider
     */
    public function test_validate_fail($value, $min, $max) {
        $integer = new Integer(array('min' => $min, 'max' => $max));
        $integer->validate($value);
    }

    /**
     * @dataProvider validateSuccessProvider
     */
    public function test_validate_success($value, $min, $max) {
        $integer = new Integer(array('min' => $min, 'max' => $max));
        $integer->validate($value);
        $this->assertTrue(true); // we simply check that no exceptions are thrown
    }
}
