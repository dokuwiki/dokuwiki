<?php

namespace plugin\struct\test;

use plugin\struct\meta;

spl_autoload_register(array('action_plugin_struct_autoloader', 'autoloader'));

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
            "schema    : testtable",
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

}
