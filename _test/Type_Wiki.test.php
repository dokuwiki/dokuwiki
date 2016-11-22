<?php

namespace dokuwiki\plugin\struct\test;

use dokuwiki\plugin\struct\types\Wiki;

/**
 * Testing the Wiki Type
 *
 * @group plugin_struct
 * @group plugins
 */
class Type_Wiki_struct_test extends StructTest {

    /**
     * Provides failing validation data
     *
     * @return array
     */
    public function validateProvider() {
        return array(
            array("  * hi\n  * ho", "  * hi\n  * ho", '')
        );
    }

    /**
     * @dataProvider validateProvider
     */
    public function test_validate($value, $expected_validated_result, $msg) {
        $wiki = new Wiki();
        $actual_validated_result = $wiki->validate($value);
        $this->assertEquals($expected_validated_result, $actual_validated_result, $msg);
    }

}
