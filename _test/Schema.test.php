<?php
namespace dokuwiki\plugin\struct\test;

use dokuwiki\plugin\struct\meta\Schema;

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
     * @covers       \dokuwiki\plugin\struct\meta\Schema::cleanTableName
     *
     * @param string $input_name
     * @param string $expected_cleaned_name
     */
    public function test_cleanTableName($input_name, $expected_cleaned_name) {
        $actual_cleaned_name = Schema::cleanTableName($input_name);
        $this->assertSame($expected_cleaned_name, $actual_cleaned_name, $input_name);
    }

    /**
     * @expectedException \dokuwiki\plugin\struct\meta\StructException
     */
    public function test_deletefail() {
        $schema = new Schema('foo');
        $schema->delete();
    }

    public function test_deleteok() {
        $this->loadSchemaJSON('schema1');

        $schema = new Schema('schema1');
        $this->assertEquals(1, $schema->getId());
        $schema->delete();
        $this->assertEquals(0, $schema->getId());

        $schema = new Schema('schema1');
        $this->assertEquals(0, $schema->getId());
    }
}
