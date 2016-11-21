<?php

namespace dokuwiki\plugin\struct\test;

use dokuwiki\plugin\struct\meta;
use dokuwiki\plugin\struct\test\mock\SearchConfig;

/**
 * @group plugin_struct
 * @group plugins
 *
 */
class SearchConfig_struct_test extends StructTest {

    public function test_filtervars_simple() {
        global $INFO;
        $INFO['id'] = 'foo:bar:baz';

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
        global $INFO;
        $INFO['id'] = 'foo:bar:baz';

        // prepare some struct data
        $sb = new meta\SchemaImporter('schema1', file_get_contents(__DIR__ . '/json/schema1.struct.json'));
        $sb->build();
        $schemaData = meta\AccessTable::byTableName('schema1', $INFO['id'], time());
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

        $this->assertEquals('pretestpost', $searchConfig->applyFilterVars('pre$STRUCT.first$post'));
        $this->assertEquals('pretestpost', $searchConfig->applyFilterVars('pre$STRUCT.alias.first$post'));
        $this->assertEquals('pretestpost', $searchConfig->applyFilterVars('pre$STRUCT.schema1.first$post'));

        $this->assertEquals(array('multi1', 'multi2'), $searchConfig->applyFilterVars('$STRUCT.second$'));
        $this->assertEquals(array('multi1', 'multi2'), $searchConfig->applyFilterVars('$STRUCT.alias.second$'));
        $this->assertEquals(array('multi1', 'multi2'), $searchConfig->applyFilterVars('$STRUCT.schema1.second$'));

        $this->assertEquals(array('premulti1post', 'premulti2post'), $searchConfig->applyFilterVars('pre$STRUCT.second$post'));
        $this->assertEquals(array('premulti1post', 'premulti2post'), $searchConfig->applyFilterVars('pre$STRUCT.alias.second$post'));
        $this->assertEquals(array('premulti1post', 'premulti2post'), $searchConfig->applyFilterVars('pre$STRUCT.schema1.second$post'));

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
