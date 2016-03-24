<?php
namespace plugin\struct\test;

// we don't have the auto loader here
spl_autoload_register(array('action_plugin_struct_autoloader', 'autoloader'));

use plugin\struct\meta\Schema;

/**
 * @group plugin_struct
 * @group plugins
 *
 */
class schema_struct_test extends StructTest {

    /**
     * Testdata for @see schema_struct_test::test_cleanTableName
     *
     * @return array
     */
    public static function cleanTableName_testdata() {
        return array(
            array(
                'abc',
                'abc',
            ),
            array(
                '123abc',
                'abc',
            ),
            array(
                'abc123',
                'abc123',
            ),
            array(
                '_a_b_c_',
                'a_b_c_',
            ),
            array(
                '-a-b-c-',
                'abc',
            ),
            array(
                '/a/b/c/',
                'abc',
            ),
            array(
                '\\a\\b\\c\\',
                'abc',
            )
        );
    }

    /**
     * @dataProvider cleanTableName_testdata
     *
     * @covers       plugin\struct\meta\Schema::cleanTableName
     *
     * @param string $input_name
     * @param string $expected_cleaned_name
     */
    public function test_cleanTableName($input_name, $expected_cleaned_name) {
        $actual_cleaned_name = Schema::cleanTableName($input_name);
        $this->assertSame($expected_cleaned_name, $actual_cleaned_name, $input_name);
    }

}
