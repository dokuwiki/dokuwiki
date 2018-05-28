<?php

namespace dokuwiki\plugin\struct\test;

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

        // This hash has to be adjusted whenever the $syntax below changes!
        $INPUT->set('hash', '01210d2574d13978f06d85106582a251');

        $syntax = '
---- struct table ----
schema: wikilookup
cols: *
----
';
        $expectedCSV = '"FirstFieldText","SecondFieldLongText","ThirdFieldWiki"
"abc def","abc
def","  * hi
  * ho"';

        $ins = p_get_instructions($syntax);
        $renderedCSV = p_render('struct_csv', $ins, $info);
        $actualCSV = str_replace("\r", '', $renderedCSV);

        $this->assertEquals(trim($expectedCSV), trim($actualCSV));
    }
}
