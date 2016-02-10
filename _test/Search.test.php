<?php

namespace plugin\struct\test;

use plugin\struct\meta;

spl_autoload_register(array('action_plugin_struct_autoloader', 'autoloader'));

class Search extends meta\Search {
    public $schemas = array();
    /** @var  meta\Column[] */
    public $columns = array();

    public $sortby = array();
}


/**
 * Tests for the building of SQL-Queries for the struct plugin
 *
 * @group plugin_struct
 * @group plugins
 *
 */
class Search_struct_test extends \DokuWikiTest {

    protected $pluginsEnabled = array('struct', 'sqlite');

    public function setUp() {
        parent::setUp();

        $sb = new meta\SchemaBuilder(
            'schema1',
            array(
                'new' => array(
                    'new1' => array('label' => 'first', 'class' => 'text', 'sort' => 10, 'ismulti' => 0),
                    'new2' => array('label' => 'second', 'class' => 'text', 'sort' => 20, 'ismulti' => 1),
                    'new3' => array('label' => 'third', 'class' => 'text', 'sort' => 30, 'ismulti' => 0),
                    'new4' => array('label' => 'fourth', 'class' => 'text', 'sort' => 40, 'ismulti' => 0),
                )
            )
        );
        $sb->build();

        $sb = new meta\SchemaBuilder(
            'schema2',
            array(
                'new' => array(
                    'new1' => array('label' => 'afirst', 'class' => 'text', 'sort' => 10, 'ismulti' => 0),
                    'new2' => array('label' => 'asecond', 'class' => 'text', 'sort' => 20, 'ismulti' => 1),
                    'new3' => array('label' => 'athird', 'class' => 'text', 'sort' => 30, 'ismulti' => 0),
                    'new4' => array('label' => 'afourth', 'class' => 'text', 'sort' => 40, 'ismulti' => 0),
                )
            )
        );
        $sb->build();

        $sd = new meta\SchemaData('schema1', 'page1', time());
        $sd->saveData(
            array(
                'first' => 'first data',
                'second' => array('second data', 'more data', 'even more'),
                'third' => 'third data',
                'fourth' => 'fourth data'
            )
        );

        $sd = new meta\SchemaData('schema2', 'page1', time());
        $sd->saveData(
            array(
                'afirst' => 'first data',
                'asecond' => array('second data', 'more data', 'even more'),
                'athird' => 'third data',
                'afourth' => 'fourth data'
            )
        );
    }


    public function test_simple() {

        /** @var \helper_plugin_struct_db $plugin */
        $plugin = plugin_load('helper', 'struct_db');
        $sqlite = $plugin->getDB();

        /*
        $res = $sqlite->query('SELECT * FROM multivals');
        $data = $sqlite->res2arr($res);
        $sqlite->res_close($res);
        print_r($data);
        */


        $search = new Search();

        $search->addSchema('schema1');
        $search->addColumn('first');
        $search->addColumn('second');

        $sql = $search->getSQL();
        echo "\n$sql\n";
    }

    public function test_search() {
        $search = new Search();

        $search->addSchema('schema1');
        $search->addSchema('schema2', 'foo');
        $this->assertEquals(2, count($search->schemas));

        $search->addColumn('first');
        $this->assertEquals('schema1', $search->columns[0]->getTable());
        $this->assertEquals(1, $search->columns[0]->getColref());

        $search->addColumn('afirst');
        $this->assertEquals('schema2', $search->columns[1]->getTable());
        $this->assertEquals(1, $search->columns[1]->getColref());

        $search->addColumn('schema1.third');
        $this->assertEquals('schema1', $search->columns[2]->getTable());
        $this->assertEquals(3, $search->columns[2]->getColref());

        $search->addColumn('foo.athird');
        $this->assertEquals('schema2', $search->columns[3]->getTable());
        $this->assertEquals(3, $search->columns[3]->getColref());

        $search->addColumn('asecond');
        $this->assertEquals('schema2', $search->columns[4]->getTable());
        $this->assertEquals(2, $search->columns[4]->getColref());

        $search->addColumn('doesntexist');
        $this->assertEquals(5, count($search->columns));

        $search->addSort('first', false);
        $this->assertEquals(1, count($search->sortby));


        $search->addFilter('second', 'sec', '~', 'AND');

        $sql = $search->getSQL();
        echo "\n$sql\n";
    }



}
