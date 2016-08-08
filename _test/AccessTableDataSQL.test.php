<?php

namespace dokuwiki\plugin\struct\test;

use dokuwiki\plugin\struct\meta\Search;

/**
 * Tests for the building of SQL-Queries for the struct plugin
 *
 * @group plugin_struct
 * @group plugins
 *
 */
class AccessTableDataSQL_struct_test extends StructTest {

    /**
     * Testdata for @see schemaDataSQL_struct_test::test_buildGetDataSQL
     *
     * @return array
     */
    public static function buildGetDataSQL_testdata() {
        $schemadata = new mock\AccessTableDataNoDB('testtable', 'pagename', 27);

        /** @noinspection SqlResolve */
        return array(
            array(
                array(
                    'obj' => $schemadata,
                    'singles' => array('dokuwiki\\plugin\\struct\\types\\Text', 'dokuwiki\\plugin\\struct\\types\\Text'),
                    'multis' => array(),
                ),
                "SELECT DATA.pid AS PID,
                        DATA.col1 AS col1,
                        DATA.col2 AS col2
                   FROM data_testtable AS DATA
                  WHERE (DATA.pid = ?
                    AND DATA.rev = ?)
               GROUP BY DATA.pid,col1,col2",
                array('pagename', 27),
                'no multis, with ts',
            ),
            array(
                array(
                    'obj' => $schemadata,
                    'singles' => array('dokuwiki\\plugin\\struct\\types\\Text','dokuwiki\\plugin\\struct\\types\\Text'),
                    'multis' => array('dokuwiki\\plugin\\struct\\types\\Text'),
                ),
                "SELECT DATA.pid AS PID,
                        DATA.col1 AS col1,
                        DATA.col2 AS col2,
                        GROUP_CONCAT(M3.value,'".Search::CONCAT_SEPARATOR."') AS col3
                   FROM data_testtable AS DATA
                   LEFT OUTER JOIN multi_testtable AS M3
                     ON DATA.pid = M3.pid
                    AND DATA.rev = M3.rev
                    AND M3.colref = 3
                  WHERE (DATA.pid = ?
                    AND DATA.rev = ?)
               GROUP BY DATA.pid,col1,col2",
                array('pagename', 27,),
                'one multi, with ts',
            ),
            array(
                array(
                    'obj' => $schemadata,
                    'singles' => array(),
                    'multis' => array('dokuwiki\\plugin\\struct\\types\\Text','dokuwiki\\plugin\\struct\\types\\Text')
                ),
                "SELECT DATA.pid AS PID,
                        GROUP_CONCAT(M1.value,'".Search::CONCAT_SEPARATOR."') AS col1,
                        GROUP_CONCAT(M2.value,'".Search::CONCAT_SEPARATOR."') AS col2
                   FROM data_testtable AS DATA
                   LEFT OUTER JOIN multi_testtable AS M2
                     ON DATA.pid = M2.pid
                    AND DATA.rev = M2.rev
                    AND M2.colref = 2
                   LEFT OUTER JOIN multi_testtable AS M1
                     ON DATA.pid = M1.pid
                    AND DATA.rev = M1.rev
                    AND M1.colref = 1
                  WHERE (DATA.pid = ?
                    AND DATA.rev = ?)
               GROUP BY DATA.pid",
                array('pagename', 27,),
                "only two multis"
            )
        );
    }

    /**
     * @dataProvider buildGetDataSQL_testdata
     *
     * @covers       dokuwiki\plugin\struct\meta\SchemaData::buildGetDataSQL
     *
     * @param string $expected_sql
     * @param string $msg
     *
     */
    public function test_buildGetDataSQL($testvals, $expected_sql, $expected_opt, $msg) {
        /** @var mock\AccessTableDataNoDB $obj */
        $obj = $testvals['obj'];
        $obj->setColumns($testvals['singles'], $testvals['multis']);

        list($actual_sql, $actual_opt) = $obj->buildGetDataSQL();

        $this->assertSame($this->cleanWS($expected_sql), $this->cleanWS($actual_sql), $msg);
        $this->assertEquals($expected_opt, $actual_opt, $msg);
    }

}
