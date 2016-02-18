<?php
namespace plugin\struct\test;

/**
 * Class helper_plugin_struct_config
 * @package plugin\struct\test
 */
class helper_plugin_struct_config extends \helper_plugin_struct_config {
    /**
     * Parse a filter
     *
     * @param string $val
     *
     * @return array ($col, $comp, $value)
     * @throws \plugin\struct\meta\StructException
     */
    public function parseFilter($val) {
        return parent::parseFilter($val);
    }
}

/**
 * Tests for the class action_plugin_magicmatcher_oldrevisions of the magicmatcher plugin
 *
 * @group plugin_struct
 * @group plugins
 *
 */
class config_helper_struct_test extends \DokuWikiTest {

    protected $pluginsEnabled = array('struct',);

    /**
     * Testdata for @see schema_struct_test::test_cleanTableName
     *
     * @return array
     */
    public static function cleanTableName_testdata() {
        return array(
            array('a=b', array(0 => 'a', 1 => '=', 2 => 'b'), ''),
            array(
                'a<b',
                array(
                    0 => 'a',
                    1 => '<',
                    2 => 'b'
                ),
                '',
            ),
            array(
                'a>b',
                array(
                    0 => 'a',
                    1 => '>',
                    2 => 'b'
                ),
                '',
            ),
            array(
                'a<=b',
                array(
                    0 => 'a',
                    1 => '<=',
                    2 => 'b'
                ),
                '',
            ),
            array(
                'a>=b',
                array(
                    0 => 'a',
                    1 => '>=',
                    2 => 'b'
                ),
                '',
            ),
            array(
                'a!=b',
                array(
                    0 => 'a',
                    1 => '!=',
                    2 => 'b'
                ),
                '',
            ),
            array('a<>b', array(0 => 'a', 1 => '!=', 2 => 'b'), ''),
            array(
                'a!~b',
                array(
                    0 => 'a',
                    1 => '!~',
                    2 => 'b'
                ),
                '',
            ),
            array(
                'a~b',
                array(
                    0 => 'a',
                    1 => '~',
                    2 => 'b'
                ),
                '',
            ),
            array('a*~b',array(0 => 'a',1 => '~',2 => '*b*'), ''),
        );
    }

    /**
     * @dataProvider cleanTableName_testdata
     *
     * @covers plugin\struct\meta\Schema::cleanTableName
     *
     * @param string $input_name
     * @param string $expected_cleaned_name
     * @param string $msg
     */
    public function test_parseFilter($input_filter, $expected_filter, $msg) {
        $confHelper = new helper_plugin_struct_config;

        $actual_filter = $confHelper->parseFilter($input_filter);

        $this->assertSame($expected_filter, $actual_filter, $input_filter . ' ' . $msg);
    }
}
