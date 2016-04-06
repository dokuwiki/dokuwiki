<?php

namespace plugin\struct\test;

use plugin\struct\types\Decimal;

spl_autoload_register(array('action_plugin_struct_autoloader', 'autoloader'));

/**
 * Testing the Decimal Type
 *
 * @group plugin_struct
 * @group plugins
 */
class Type_Decimal_struct_test extends StructTest {

    /**
     * Provides failing validation data
     *
     * @return array
     */
    public function validateFailProvider() {
        return array(
            // same as integer:
            array('foo', '', ''),
            array('foo222', '', ''),
            array('-5', '0', ''),
            array('5', '', '0'),
            array('500', '100', '200'),
            array('50', '100', '200'),
            // decimal specifics
            array('5.5', '5.6', ''),
            array('5,5', '5.6', ''),
            array('-5.5', '-5.4', ''),
            array('-5,5', '-5.4', ''),
        );
    }

    /**
     * Provides successful validation data
     *
     * @return array
     */
    public function validateSuccessProvider() {
        return array(
            // same as integer
            array('0', '', ''),
            array('-5', '', ''),
            array('5', '', ''),
            array('5', '0', ''),
            array('-5', '', '0'),
            array('150', '100', '200'),
            // decimal specifics
            array('5.5', '', ''),
            array('5,5', '', ''),
            array('-5.5', '', ''),
            array('-5,5', '', ''),
            array('5.5', '4.5', ''),
            array('5,5', '4.5', ''),
            array('-5.5', '', '4.5'),
            array('-5,5', '', '4.5'),
            array('5.5645000', '', ''),
        );
    }


    /**
     * @expectedException \plugin\struct\meta\ValidationException
     * @dataProvider validateFailProvider
     */
    public function test_validate_fail($value, $min, $max) {
        $decimal = new Decimal(array('min' => $min, 'max' => $max));
        $decimal->validate($value);
    }

    /**
     * @dataProvider validateSuccessProvider
     */
    public function test_validate_success($value, $min, $max, $decpoint = '.') {
        $decimal = new Decimal(array('min' => $min, 'max' => $max));
        $decimal->validate($value);
        $this->assertTrue(true); // we simply check that no exceptions are thrown
    }


    public function valueProvider() {
        return array(
            // $value, $expect, $roundto, $decpoint, $thousands, $trimzeros
            array('5000', '5 000,00', '2', ',', ' ', false),
            array('5000', '5 000', '2', ',', ' ', true),
            array('5000', '5 000', '0', ',', ' ', false),
            array('5000', '5 000', '0', ',', ' ', true),
            array('5000', '5 000', '-1', ',', ' ', false),
            array('5000', '5 000', '-1', ',', ' ', true),

            array('-0.55600', '-0,56', '2', ',', ' ', false),
            array('-0.55600', '-0,55600', '-1', ',', ' ', false),
            array('-0.55600', '-0,556', '-1', ',', ' ', true),
            array('-0.55600', '-0,5560', '4', ',', ' ', false),
            array('-0.55600', '-0,556', '4', ',', ' ', true),
        );
    }

    /**
     * @dataProvider valueProvider
     */
    public function test_renderValue($value, $expect, $roundto, $decpoint, $thousands, $trimzeros ) {
        $decimal = new Decimal(array(
                                   'roundto' => $roundto,
                                   'decpoint' => $decpoint,
                                   'thousands' => $thousands,
                                   'trimzeros' => $trimzeros
                               ));
        $R = new \Doku_Renderer_xhtml();
        $R->doc = '';
        $decimal->renderValue($value, $R, 'xhtml');
        $this->assertEquals($expect, $R->doc);
    }
}
