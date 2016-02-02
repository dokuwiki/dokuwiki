<?php

use plugin\struct\meta\SchemaData;

/**
 * Tests for the building of SQL-Queries for the struct plugin
 *
 * @group plugin_struct
 * @group plugins
 *
 */
class schemaDataSQL_struct_test extends DokuWikiTest {

    protected $pluginsEnabled = array('struct',);

    /**
     * Testdata for @see schemaDataSQL_struct_test::test_buildGetDataSQL
     *
     * @return array
     */
    public static function buildGetDataSQL_testdata() {
        return array(
            array(
                array(
                    'table' => 'data_testtable',
                    'colsel' => 'col1,col2',
                    'multis' => array(),
                    'page' => 'pagename',
                    'ts' => '27',
                ),
                "SELECT col1,col2 FROM data_testtable DATA
 WHERE DATA.pid = ? AND DATA.rev = ?",
                array('pagename', 27,),
                'no multis, with ts',
            ),
            array(
                array(
                    'table' => 'data_testtable',
                    'colsel' => 'col1,col2',
                    'multis' => array(3,),
                    'page' => 'pagename',
                    'ts' => '27',
                ),
                "SELECT col1,col2,M3.value AS col3 FROM data_testtable DATA
LEFT OUTER JOIN multivals M3 ON DATA.pid = M3.pid AND DATA.rev = M3.rev AND M3.tbl = 'data_testtable' AND M3.colref = 3
 WHERE DATA.pid = ? AND DATA.rev = ?",
                array('pagename', 27,),
                'one multi, with ts',
            ),
        );
    }

    /**
     * @dataProvider buildGetDataSQL_testdata
     *
     * @covers       plugin\struct\meta\SchemaData::buildGetDataSQL
     *
     * @param string $expected_sql
     * @param string $msg
     *
     */
    public function test_buildGetDataSQL($testvals, $expected_sql, $expected_opt, $msg) {
        list($actual_sql, $actual_opt) = SchemaData::buildGetDataSQL($testvals['table'], $testvals['colsel'], $testvals['multis'], $testvals['page'], $testvals['ts']);

        $this->assertSame($expected_sql, $actual_sql, $msg);
        $this->assertEquals($expected_opt, $actual_opt, $msg);
    }

    /**
     * Testdata for @see schemaDataSQL_struct_test::test_consolidateData
     *
     * @return array
     */
    public static function consolidateData_testdata() {
        return array(
            array(
                array(
                    array('col1' => 'value1', 'col2' => 'value2.1',),
                    array('col1' => 'value1', 'col2' => 'value2.2',),
                ),
                array('1' => 'columnname1', '2' => 'columnname2',),
                array('columnname1' => 'value1',
                    'columnname2' => array('value2.1', 'value2.2',),),
                '',
            ),
        );
    }

    /**
     * @dataProvider consolidateData_testdata
     *
     * @param array  $testdata
     * @param array  $testlabels
     * @param array  $expected_data
     * @param string $msg
     */
    public function test_consolidateData($testdata, $testlabels, $expected_data, $msg){

        // act
        $actual_data = SchemaData::consolidateData($testdata, $testlabels);

        // assert
        $this->assertEquals($expected_data, $actual_data, $msg);
    }
}
