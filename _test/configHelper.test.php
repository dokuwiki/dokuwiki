<?php
namespace plugin\struct\test;

// we don't have the auto loader here
spl_autoload_register(array('action_plugin_struct_autoloader', 'autoloader'));

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
     * @covers       plugin\struct\meta\Schema::cleanTableName
     *
     * @param $input_filter
     * @param $expected_filter
     * @param string $msg
     */
    public function test_parseFilter($input_filter, $expected_filter, $msg) {
        $confHelper = new mock\helper_plugin_struct_config();

        $actual_filter = $confHelper->parseFilter($input_filter);

        $this->assertSame($expected_filter, $actual_filter, $input_filter . ' ' . $msg);
    }
}
