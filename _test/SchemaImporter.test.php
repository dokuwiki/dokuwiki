<?php

namespace plugin\struct\test;

use plugin\struct\meta;

spl_autoload_register(array('action_plugin_struct_autoloader', 'autoloader'));

/**
 * @group plugin_struct
 * @group plugins
 *
 */
class SchemaImporter_struct_test extends StructTest {

    public function test_export() {
        $sb = new meta\SchemaBuilder(
            'schema1',
            array(
                'new' => array(
                    'new1' => array('label' => 'first', 'class' => 'Text', 'sort' => 10, 'ismulti' => 0, 'isenabled' => 1),
                    'new2' => array('label' => 'second', 'class' => 'Text', 'sort' => 20, 'ismulti' => 1, 'isenabled' => 1),
                    'new3' => array('label' => 'third', 'class' => 'Text', 'sort' => 30, 'ismulti' => 0, 'isenabled' => 1),
                    'new4' => array('label' => 'fourth', 'class' => 'Text', 'sort' => 40, 'ismulti' => 0, 'isenabled' => 1),
                )
            )
        );
        $sb->build();

        $schema = new meta\Schema('schema1');
        $expect = json_decode(file_get_contents(__DIR__ . '/json/schema1.struct.json'), true);
        $actual = json_decode($schema->toJSON(), true);
        // we don't expect this to match
        unset($expect['structversion']);
        unset($actual['structversion']);
        $this->assertEquals($expect, $actual);
    }

    public function test_import_one() {
        $sb = new meta\SchemaImporter('tag', file_get_contents(__DIR__ . '/json/tag.struct.json'));
        $this->assertTrue((bool) $sb->build());

        $schema = new meta\Schema('tag');
        $columns = $schema->getColumns();

        $this->assertEquals(2, count($columns));
        $this->assertTrue(is_a($columns[0], '\plugin\struct\meta\Column'));
        $this->assertTrue(is_a($columns[1], '\plugin\struct\meta\Column'));
        $this->assertEquals('tag', $columns[0]->getLabel());
        $this->assertEquals('tags', $columns[1]->getLabel());
    }

    public function test_import_export() {
        $sb = new meta\SchemaImporter('foobar', file_get_contents(__DIR__ . '/json/schema1.struct.json'));
        $this->assertTrue((bool) $sb->build());

        $schema = new meta\Schema('foobar');
        $expect = json_decode(file_get_contents(__DIR__ . '/json/schema1.struct.json'), true);
        $actual = json_decode($schema->toJSON(), true);
        // we don't expect this to match
        unset($expect['structversion']);
        unset($actual['structversion']);
        $expect['schema'] = 'foobar'; // we exported the new schema
        $this->assertEquals($expect, $actual);
    }

}
