<?php

namespace dokuwiki\plugin\struct\test;

use dokuwiki\plugin\struct\meta;

/**
 * Tests for parsing the aggregation config for the struct plugin
 *
 * @group plugin_struct
 * @group plugins
 *
 */
class ConfigParser_struct_test extends StructTest {

    public function test_simple() {
        $lines = array(
            "schema    : testtable, another, foo bar",
            "cols      : %pageid%, count",
            "sort      : ^count",
            "sort      : %pageid%"
        );

        $configParser = new meta\ConfigParser($lines);
        $actual_config = $configParser->getConfig();

        $expected_config = array(
            'limit' => 0,
            'dynfilters' => false,
            'summarize' => false,
            'rownumbers' => false,
            'sepbyheaders' => false,
            'headers' =>
                array(
                    0 => NULL,
                    1 => NULL,
                ),
            'widths' =>
                array(),
            'filter' =>
                array(),
            'schemas' =>
                array(
                    0 =>
                        array(
                            0 => 'testtable',
                            1 => '',
                        ),
                    1 =>
                        array(
                            0 => 'another',
                            1 => '',
                        ),
                    2 =>
                        array(
                            0 => 'foo',
                            1 => 'bar',
                        ),
                ),
            'cols' =>
                array(
                    0 => '%pageid%',
                    1 => 'count',
                ),
            'sort' =>
                array(
                    array(
                        0 => 'count',
                        1 => false,
                    ),
                    array(
                        0 => '%pageid%',
                        1 => true,
                    )
                ),
        );

        $this->assertEquals($expected_config, $actual_config);
    }

    public function test_width() {
        $lines = array('width: 5, 15px, 23.4em, meh, 10em');

        $configParser = new meta\ConfigParser($lines);

        $config = $configParser->getConfig();

        $this->assertEquals(
            array('5px', '15px', '23.4em', '', '10em'),
            $config['widths']
        );
    }
}
