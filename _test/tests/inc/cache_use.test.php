<?php

/**
 * Class cache_use_test
 *
 * Tests if caching can actually be used
 */
class cache_use_test extends DokuWikiTest {
    /** @var cache_renderer $cache */
    private $cache;

    function setUp() {
        global $ID, $conf;
        parent::setUp();

        $ID = 'cached';
        $file = wikiFN($ID);
        $conf['cachetime'] = 0;  // ensure the value is not -1, which disables caching

        saveWikiText($ID, 'Content', 'Created');
        // set the modification time a second in the past in order to ensure that the cache is newer than the page
        touch($file, time()-1);

        $this->cache = new cache_renderer($ID, $file, 'xhtml');
        $this->cache->storeCache('Test');
    }

    function test_use() {
        $this->assertTrue($this->cache->useCache());
    }

    /**
     * In all the following tests the cache should not be usable
     * as such, they are meaningless if test_use didn't pass.
     *
     * @depends test_use
     */
    function test_purge() {
        /* @var Input $INPUT */
        global $INPUT;
        $INPUT->set('purge',1);

        $this->assertFalse($this->cache->useCache());
        $this->assertNotEmpty($this->cache->depends['purge']);
    }

    /**
     * @depends test_use
     */
    function test_filedependency() {
        // give the dependent src file the same mtime as the cache
        touch($this->cache->file, filemtime($this->cache->cache));
        $this->assertFalse($this->cache->useCache());
    }

    /**
     * @depends test_use
     */
    function test_age() {
        // need to age both our source file & the cache
        $age = 10;
        $time = time() - $age - 1;  // older than age

        touch($this->cache->file, $time - 1);
        touch($this->cache->cache, $time);

        $this->assertFalse($this->cache->useCache(array('age' => $age)));
    }

    /**
     * @depends test_use
     */
    function test_confnocaching() {
        global $conf;
        $conf['cachetime'] = -1;   // disables renderer caching

        $this->assertFalse($this->cache->useCache());
        $this->assertNotEmpty($this->cache->_nocache);
    }
}