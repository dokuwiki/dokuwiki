<?php

namespace plugin\struct\test;

use plugin\struct\meta;
use plugin\struct\test\mock\SearchConfig;

spl_autoload_register(array('action_plugin_struct_autoloader', 'autoloader'));

/**
 * @group plugin_struct
 * @group plugins
 *
 */
class SearchConfig_struct_test extends \DokuWikiTest {

    protected $pluginsEnabled = array('struct', 'sqlite');

    protected function tearDown() {
        parent::tearDown();

        /** @var \helper_plugin_struct_db $sqlite */
        $sqlite = plugin_load('helper', 'struct_db');
        $sqlite->resetDB();
    }

    public function test_filtervars_simple() {
        global $ID;
        $ID = 'foo:bar:baz';

        $searchConfig = new SearchConfig(array());

        $this->assertEquals('foo:bar:baz', $searchConfig->applyFilterVars('$ID$'));
        $this->assertEquals('baz', $searchConfig->applyFilterVars('$PAGE$'));
        $this->assertEquals('foo:bar', $searchConfig->applyFilterVars('$NS$'));
        $this->assertEquals(date('Y-m-d'), $searchConfig->applyFilterVars('$TODAY$'));
        $this->assertEquals('', $searchConfig->applyFilterVars('$USER$'));
        $_SERVER['REMOTE_USER'] = 'user';
        $this->assertEquals('user', $searchConfig->applyFilterVars('$USER$'));

        $this->assertEquals('user baz', $searchConfig->applyFilterVars('$USER$ $PAGE$'));
        $this->assertEquals('$user', $searchConfig->applyFilterVars('$user'));

    }

    public function test_filtervars_struct() {
        global $ID;
        $ID = 'foo:bar:baz';

        // prepare some struct data
        $sb = new meta\SchemaImporter('schema1', file_get_contents(__DIR__ . '/json/schema1.schema.json'));
        $sb->build();
        $schemaData = new meta\SchemaData('schema1', $ID, time());
        $schemaData->saveData(
            array(
                'first' => 'test',
                'second' => array('multi1', 'multi2')
            )
        );

        $searchConfig = new SearchConfig(array('schemas' => array(array('schema1', 'alias'))));
        $this->assertEquals('test', $searchConfig->applyFilterVars('$STRUCT.first$'));
        $this->assertEquals('test', $searchConfig->applyFilterVars('$STRUCT.alias.first$'));
        $this->assertEquals('test', $searchConfig->applyFilterVars('$STRUCT.schema1.first$'));
        $this->assertEquals('multi1', $searchConfig->applyFilterVars('$STRUCT.second$'));
        $this->assertEquals('multi1', $searchConfig->applyFilterVars('$STRUCT.alias.second$'));
        $this->assertEquals('multi1', $searchConfig->applyFilterVars('$STRUCT.schema1.second$'));

        $this->assertEquals('', $searchConfig->applyFilterVars('$STRUCT.notexisting$'));
    }

    public function test_cacheflags() {
        $searchConfig = new SearchConfig(array());

        $flag = $searchConfig->determineCacheFlag(array('foo', 'bar'));
        $this->assertTrue((bool) ($flag & SearchConfig::$CACHE_DEFAULT));
        $this->assertFalse((bool) ($flag & SearchConfig::$CACHE_USER));
        $this->assertFalse((bool) ($flag & SearchConfig::$CACHE_DATE));

        $flag = $searchConfig->determineCacheFlag(array('foo', '$USER$'));
        $this->assertTrue((bool) ($flag & SearchConfig::$CACHE_DEFAULT));
        $this->assertTrue((bool) ($flag & SearchConfig::$CACHE_USER));
        $this->assertFalse((bool) ($flag & SearchConfig::$CACHE_DATE));

        $flag = $searchConfig->determineCacheFlag(array('foo', '$TODAY$'));
        $this->assertTrue((bool) ($flag & SearchConfig::$CACHE_DEFAULT));
        $this->assertFalse((bool) ($flag & SearchConfig::$CACHE_USER));
        $this->assertTrue((bool) ($flag & SearchConfig::$CACHE_DATE));

        $flag = $searchConfig->determineCacheFlag(array('foo', '$TODAY$', '$USER$'));
        $this->assertTrue((bool) ($flag & SearchConfig::$CACHE_DEFAULT));
        $this->assertTrue((bool) ($flag & SearchConfig::$CACHE_USER));
        $this->assertTrue((bool) ($flag & SearchConfig::$CACHE_DATE));
    }
}
