<?php

use plugin\struct\meta\Schema;

/**
 * Tests for the class action_plugin_magicmatcher_oldrevisions of the magicmatcher plugin
 *
 * @group plugin_struct
 * @group plugins
 *
 */
class schema_struct_test extends DokuWikiTest {

    protected $pluginsEnabled = array('struct');

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
                'abc',
            ),
            array(
                '123abc',
                'abc',
                '123abc',
            ),
            array(
                'abc123',
                'abc123',
                'abc123',
            ),
            array(
                '_a_b_c_',
                'a_b_c_',
                '_a_b_c_',
            ),
            array(
                '-a-b-c-',
                'abc',
                '-a-b-c-',
            ),
            array(
                '/a/b/c/',
                'abc',
                '/a/b/c/',
            ),
            array(
                '\\a\\b\\c\\',
                'abc',
                '\\a\\b\\c\\',
            )
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
    public function test_cleanTableName($input_name, $expected_cleaned_name, $msg) {


        $actual_cleaned_name = Schema::cleanTableName($input_name);

        $this->assertSame($expected_cleaned_name, $actual_cleaned_name, $msg);
    }
}
