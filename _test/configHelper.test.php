<?php
namespace dokuwiki\plugin\struct\test;

/**
 * @group plugin_struct
 * @group plugins
 *
 */
class config_helper_struct_test extends StructTest {

    public static function filter_testdata() {
        return array(
            array('a=b', array(0 => 'a', 1 => '=', 2 => 'b'), false, ''),
            array('a<b', array(0 => 'a', 1 => '<', 2 => 'b'), false, ''),
            array('a>b', array(0 => 'a', 1 => '>', 2 => 'b'), false, ''),
            array('a<=b', array(0 => 'a', 1 => '<=', 2 => 'b'), false, ''),
            array('a>=b', array(0 => 'a', 1 => '>=', 2 => 'b'), false, ''),
            array('a!=b', array(0 => 'a', 1 => '!=', 2 => 'b'), false, ''),
            array('a<>b', array(0 => 'a', 1 => '<>', 2 => 'b'), false, ''),
            array('a!~b', array(0 => 'a', 1 => '!~', 2 => 'b'), false, ''),
            array('a~b', array(0 => 'a', 1 => '~', 2 => 'b'), false, ''),
            array('a*~b', array(0 => 'a', 1 => '*~', 2 => 'b'), false, ''),
            array('a?b', array(), '\dokuwiki\plugin\struct\meta\StructException', 'Exception should be thrown on unknown operator')
        );
    }

    /**
     * @dataProvider filter_testdata
     *
     * @param $input_filter
     * @param $expected_filter
     * @param string $msg
     */
    public function test_parseFilter($input_filter, $expected_filter, $expectException, $msg) {
        $confHelper = new mock\helper_plugin_struct_config();
        if($expectException !== false) $this->setExpectedException($expectException);

        $actual_filter = $confHelper->parseFilter($input_filter);

        $this->assertSame($expected_filter, $actual_filter, $input_filter . ' ' . $msg);
    }

    public function test_parseSort_asc() {
        /** @var \helper_plugin_struct_config $confHelper */
        $confHelper = plugin_load('helper', 'struct_config');
        $teststring = "column";

        $actual_sort = $confHelper->parseSort($teststring);

        $this->assertEquals(array($teststring, true), $actual_sort);
    }

    public function test_parseSort_desc() {
        /** @var \helper_plugin_struct_config $confHelper */
        $confHelper = plugin_load('helper', 'struct_config');
        $teststring = "^column";

        $actual_sort = $confHelper->parseSort($teststring);

        $this->assertEquals(array('column', false), $actual_sort);
    }
}
