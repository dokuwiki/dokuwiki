<?php

namespace dokuwiki\plugin\struct\test;

use dokuwiki\plugin\struct\meta\Schema;
use dokuwiki\plugin\struct\meta\ValidationException;
use dokuwiki\plugin\struct\types\Color;
use dokuwiki\plugin\struct\types\Date;
use dokuwiki\plugin\struct\types\Tag;

/**
 * @group plugin_struct
 * @group plugins
 */
class Type_Color_struct_test extends StructTest {

    /**
     * DataProvider for successful validations
     */
    public function validate_success() {
        return array(
            array('#123abc', '#123abc'),
            array('#123abc ', '#123abc'),
            array(' #123abc', '#123abc'),
            array(' #123abc ', '#123abc'),

            array('#123EDF', '#123edf'),
            array('#123EDF ', '#123edf'),
            array(' #123EDF', '#123edf'),
            array(' #123EDF ', '#123edf'),

            array('#ffffff', ''),
            array(' #ffffff', ''),
            array('#ffffff ', ''),
            array(' #ffffff ', ''),
        );
    }

    /**
     * @dataProvider validate_success
     */
    public function test_validation_success($input, $expect) {
        $date = new Color();

        $this->assertEquals($expect, $date->validate($input));
    }


    /**
     * DataProvider for failed validations
     */
    public function validate_fail() {
        return array(
            array('ffffff'),
            array('foo bar'),
            array('#ccc'),
        );
    }

    /**
     * @dataProvider validate_fail
     * @expectedException \dokuwiki\plugin\struct\meta\ValidationException
     */
    public function test_validation_fail($input) {
        $date = new Color();

        $date->validate($input);
    }
}
