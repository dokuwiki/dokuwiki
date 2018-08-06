<?php

namespace dokuwiki\plugin\struct\test;

use dokuwiki\plugin\struct\meta\ConfigParser;

/**
 * Testing the CSV exports of aggregations
 *
 * @group plugin_struct
 * @group plugins
 */
class AggregationExportCSV extends StructTest
{

    public function setUp() {
        parent::setUp();

        $this->loadSchemaJSON('wikilookup', '', 0, true);

        /** @var \helper_plugin_struct $helper */
        $helper = plugin_load('helper', 'struct');

        $saveDate = [
            'FirstFieldText' => 'abc def',
            'SecondFieldLongText' => "abc\ndef\n",
            'ThirdFieldWiki' => "  * hi\n  * ho",
        ];
        $helper->saveLookupData('wikilookup', $saveDate);
    }

    public function test_wikiColumn()
    {
        global $INPUT;

        $syntaxPrefix = ['---- struct table ----'];
        $syntaxConfig = ['schema: wikilookup', 'cols: *'];
        $syntaxPostFix = ['----'];
        $syntax = implode("\n", array_merge($syntaxPrefix, $syntaxConfig, $syntaxPostFix));
        $expectedCSV = '"FirstFieldText","SecondFieldLongText","ThirdFieldWiki"
"abc def","abc
def","  * hi
  * ho"';

        $configParser = new ConfigParser($syntaxConfig);
        $INPUT->set('hash', md5(var_export($configParser->getConfig(), true)));

        $ins = p_get_instructions($syntax);
        $renderedCSV = p_render('struct_csv', $ins, $info);
        $actualCSV = str_replace("\r", '', $renderedCSV);

        $this->assertEquals(trim($expectedCSV), trim($actualCSV));
    }
}
