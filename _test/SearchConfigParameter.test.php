<?php

namespace dokuwiki\plugin\struct\test;

use dokuwiki\plugin\struct\meta;

/**
 * Tests handling dynamic search parameters
 *
 * @group plugin_struct
 * @group plugins
 *
 */
class SearchConfigParameter_struct_test extends StructTest {

    public function setUp() {
        parent::setUp();

        $this->loadSchemaJSON('schema1');
        $this->loadSchemaJSON('schema2');

        $as = mock\Assignments::getInstance();

        $as->assignPageSchema('page01', 'schema1');
        $this->saveData(
            'page01',
            'schema1',
            array(
                'first' => 'first data',
                'second' => array('second data', 'more data', 'even more'),
                'third' => 'third data',
                'fourth' => 'fourth data'
            )
        );

        $as->assignPageSchema('page01', 'schema2');
        $this->saveData(
            'page01',
            'schema2',
            array(
                'afirst' => 'first data',
                'asecond' => array('second data', 'more data', 'even more'),
                'athird' => 'third data',
                'afourth' => 'fourth data'
            )
        );

        for($i=10; $i <=20; $i++) {
            $as->assignPageSchema("page$i", 'schema2');
            $this->saveData(
                "page$i",
                'schema2',
                array(
                    'afirst' => "page$i first data",
                    'asecond' => array("page$i second data"),
                    'athird' => "page$i third data",
                    'afourth' => "page$i fourth data"
                )
            );
        }
    }

    public function test_constructor() {
        global $INPUT;

        $data = array(
            'schemas' => array(
                array('schema1', 'alias1'),
                array('schema2', 'alias2'),
            ),
            'cols' => array(
                '%pageid%',
                'first', 'second', 'third', 'fourth',
                'afirst', 'asecond', 'athird', 'afourth',
            )
        );

        // init with no parameters
        $expect = $data;
        $params = array();
        $searchConfig = new meta\SearchConfig($data);
        $dynamic = $searchConfig->getDynamicParameters();
        $this->assertEquals($expect, $searchConfig->getConf());
        $this->assertEquals($params, $dynamic->getURLParameters());

        // init with sort
        $INPUT->set(meta\SearchConfigParameters::$PARAM_SORT, '^alias2.athird');
        $expect['sort'] = array(array('schema2.athird', false));
        $params[meta\SearchConfigParameters::$PARAM_SORT] = '^schema2.athird';
        $searchConfig = new meta\SearchConfig($data);
        $dynamic = $searchConfig->getDynamicParameters();
        $this->assertEquals($expect, $searchConfig->getConf());
        $this->assertEquals($params, $dynamic->getURLParameters());

        // init with offset
        $INPUT->set(meta\SearchConfigParameters::$PARAM_OFFSET, 25);
        $expect['offset'] = 25;
        $params[meta\SearchConfigParameters::$PARAM_OFFSET] = 25;
        $searchConfig = new meta\SearchConfig($data);
        $dynamic = $searchConfig->getDynamicParameters();
        $this->assertEquals($expect, $searchConfig->getConf());
        $this->assertEquals($params, $dynamic->getURLParameters());

        // init with filters
        $_REQUEST[meta\SearchConfigParameters::$PARAM_FILTER]['alias1.first*~'] = 'test';
        $_REQUEST[meta\SearchConfigParameters::$PARAM_FILTER]['afirst='] = 'test2';
        $expect['filter'] = array(
            array('schema1.first', '*~', 'test', 'AND'),
            array('schema2.afirst', '=', 'test2', 'AND')
        );
        $params[meta\SearchConfigParameters::$PARAM_FILTER .'[schema1.first*~]'] = 'test';
        $params[meta\SearchConfigParameters::$PARAM_FILTER .'[schema2.afirst=]'] = 'test2';
        $searchConfig = new meta\SearchConfig($data);
        $dynamic = $searchConfig->getDynamicParameters();
        $this->assertEquals($expect, $searchConfig->getConf());
        $this->assertEquals($params, $dynamic->getURLParameters());
    }

    public function test_filter() {
        $data = array(
            'schemas' => array(
                array('schema1', 'alias1'),
                array('schema2', 'alias2'),
            ),
            'cols' => array(
                '%pageid%',
                'first', 'second', 'third', 'fourth',
                'afirst', 'asecond', 'athird', 'afourth',
            )
        );

        $searchConfig = new meta\SearchConfig($data);
        $dynamic = $searchConfig->getDynamicParameters();
        $expect = array();
        $this->assertEquals($expect, $dynamic->getFilters());

        $dynamic->addFilter('first', '*~', 'test');
        $expect = array('schema1.first' => array('*~', 'test'));
        $this->assertEquals($expect, $dynamic->getFilters());

        $dynamic->addFilter('asecond', '*~', 'test2');
        $expect = array('schema1.first' => array('*~', 'test'), 'schema2.asecond' => array('*~', 'test2'));
        $this->assertEquals($expect, $dynamic->getFilters());

        // overwrite a filter
        $dynamic->addFilter('asecond', '*~', 'foobar');
        $expect = array('schema1.first' => array('*~', 'test'), 'schema2.asecond' => array('*~', 'foobar'));
        $this->assertEquals($expect, $dynamic->getFilters());

        // overwrite a filter with blank removes
        $dynamic->addFilter('asecond', '*~', '');
        $expect = array('schema1.first' => array('*~', 'test'));
        $this->assertEquals($expect, $dynamic->getFilters());

        // adding unknown filter does nothing
        $dynamic->addFilter('nope', '*~', 'foobar');
        $expect = array('schema1.first' => array('*~', 'test'));
        $this->assertEquals($expect, $dynamic->getFilters());

        // removing unknown column does nothing
        $dynamic->removeFilter('nope');
        $expect = array('schema1.first' => array('*~', 'test'));
        $this->assertEquals($expect, $dynamic->getFilters());

        $dynamic->removeFilter('first');
        $expect = array();
        $this->assertEquals($expect, $dynamic->getFilters());
    }

    public function test_sort() {
        $data = array(
            'schemas' => array(
                array('schema1', 'alias1'),
                array('schema2', 'alias2'),
            ),
            'cols' => array(
                '%pageid%',
                'first', 'second', 'third', 'fourth',
                'afirst', 'asecond', 'athird', 'afourth',
            )
        );

        $searchConfig = new meta\SearchConfig($data);
        $dynamic = $searchConfig->getDynamicParameters();

        $dynamic->setSort('%pageid%', true);
        $conf = $dynamic->updateConfig($data);
        $param = $dynamic->getURLParameters();
        $this->assertEquals(array(array('%pageid%', true)), $conf['sort']);
        $this->assertArrayHasKey(meta\SearchConfigParameters::$PARAM_SORT, $param);
        $this->assertEquals('%pageid%', $param[meta\SearchConfigParameters::$PARAM_SORT]);

        $dynamic->setSort('%pageid%', false);
        $conf = $dynamic->updateConfig($data);
        $param = $dynamic->getURLParameters();
        $this->assertEquals(array(array('%pageid%', false)), $conf['sort']);
        $this->assertArrayHasKey(meta\SearchConfigParameters::$PARAM_SORT, $param);
        $this->assertEquals('^%pageid%', $param[meta\SearchConfigParameters::$PARAM_SORT]);

        $dynamic->removeSort();
        $conf = $dynamic->updateConfig($data);
        $param = $dynamic->getURLParameters();
        $this->assertArrayNotHasKey('sort', $conf);
        $this->assertArrayNotHasKey(meta\SearchConfigParameters::$PARAM_SORT, $param);
    }

    public function test_pagination() {
        global $INPUT;

        $data = array(
            'schemas' => array(
                array('schema2', 'alias2'),
            ),
            'cols' => array(
                'afirst'
            ),
            'rownumbers' => '1',
            'limit' => '5',
        );

        $R = new \Doku_Renderer_xhtml();
        // init with offset
        $INPUT->set(meta\SearchConfigParameters::$PARAM_OFFSET, 5);
        //$params[meta\SearchConfigParameters::$PARAM_OFFSET] = 25;
        $searchConfig = new meta\SearchConfig($data);
        $aggregationTable = new meta\AggregationTable('test_pagination', 'xhtml', $R, $searchConfig);
        $aggregationTable->render();
        $expect = '<div class="structaggregation"><div class="table"><table class="inline">
	<thead>
	<tr class="row0">
		<th class="col0">#</th><th  data-field="schema2.afirst"><a href="/./doku.php?id=test_pagination&amp;ofs=5&amp;srt=schema2.afirst" class="" title="Sort by this column">afirst</a></th>
	</tr>
	</thead>
	<tbody>
	<tr class="row1" data-pid="page14"><td class="col0">6</td><td class="col1">page14 first data</td>
	</tr>
	<tr class="row2" data-pid="page15"><td class="col0">7</td><td class="col1">page15 first data</td>
	</tr>
	<tr class="row3" data-pid="page16"><td class="col0">8</td><td class="col1">page16 first data</td>
	</tr>
	<tr class="row4" data-pid="page17"><td class="col0">9</td><td class="col1">page17 first data</td>
	</tr>
	<tr class="row5" data-pid="page18"><td class="col0">10</td><td class="col1">page18 first data</td>
	</tr>
	</tbody>
	<tfoot>
	<tr class="row6">
		<th class="col0" colspan="2"><a href="/./doku.php?id=test_pagination" class="prev">Previous page</a><a href="/./doku.php?id=test_pagination&amp;ofs=10" class="next">Next page</a></th>
	</tr>
	</tfoot>
</table></div>
</div>';

        $this->assertEquals($expect, $R->doc);
    }
}
