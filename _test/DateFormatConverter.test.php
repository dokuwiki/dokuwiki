<?php

namespace dokuwiki\plugin\struct\test;

use dokuwiki\plugin\struct\meta\Column;
use dokuwiki\plugin\struct\meta\DateFormatConverter;
use dokuwiki\plugin\struct\meta\Value;
use dokuwiki\plugin\struct\types\Text;

/**
 * @group plugin_struct
 * @group plugins
 */
class DateFormatConverter_struct_test extends StructTest {


    public function data_todate() {
        return array(
            array('Sometime %H:%M:%S %%', '\\S\\o\\m\\e\\t\\i\\m\\e H:i:s %'),
        );
    }

    /**
     * @dataProvider data_todate
     */
    public function test_todate($input, $expect) {
        $this->assertEquals($expect, DateFormatConverter::toDate($input));
    }


    public function data_tostrftime() {
        return array(
            array('\\S\\o\\m\\e\\t\\i\\m\\e H:i:s %', 'Sometime %H:%M:%S %%'),
        );
    }

    /**
     * @dataProvider data_tostrftime
     */
    public function test_tostrftime($input, $expect) {
        $this->assertEquals($expect, DateFormatConverter::toStrftime($input));
    }
}
