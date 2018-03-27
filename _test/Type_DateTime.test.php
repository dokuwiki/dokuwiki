<?php

namespace dokuwiki\plugin\struct\test;

use dokuwiki\plugin\struct\types\DateTime;

/**
 * @group plugin_struct
 * @group plugins
 */
class Type_DateTime_struct_test extends StructTest {

    /**
     * DataProvider for successful validations
     */
    public function validate_success() {
        return array(
            array('2017-04-12', '2017-04-12 00:00'),
            array('2017-04-12 ', '2017-04-12 00:00'),
            array(' 2017-04-12 ', '2017-04-12 00:00'),
            array('2017-04-12 10:11', '2017-04-12 10:11'),
            array('2017-04-12 10:11:12', '2017-04-12 10:11'),
            array('2017-04-12 whatever', '2017-04-12 00:00'),
            array('2017-4-3', '2017-04-03 00:00'),
            array('917-4-3', '917-04-03 00:00'),
        );
    }

    /**
     * @dataProvider validate_success
     */
    public function test_validation_success($input, $expect) {
        $date = new DateTime();

        $this->assertEquals($expect, $date->validate($input));
    }

    /**
     * DataProvider for failed validations
     */
    public function validate_fail() {
        return array(
            array('2017-02-31'),
            array('2017-13-31'),
            array('2017-04-12 24:00'),
            array('2017-04-12 23:70'),
        );
    }

    /**
     * @dataProvider validate_fail
     * @expectedException \dokuwiki\plugin\struct\meta\ValidationException
     */
    public function test_validation_fail($input) {
        $date = new DateTime();

        $date->validate($input);
    }
}
