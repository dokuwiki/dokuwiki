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
        global $ID;
        parent::setUp();

        $ID = 'cached';
        $file = wikiFN($ID);

        saveWikiText($ID, 'Content', 'Created');
        // set the modification time a second in the past in order to ensure that the cache is newer than the page
        touch($file, time()-1);

        # Create cache. Note that the metadata cache is used as the xhtml cache triggers metadata rendering
        $this->cache = new cache_renderer($ID, $file, 'metadata');
        $this->cache->storeCache('Test');
    }

    function test_use() {
        $this->assertTrue($this->cache->useCache());
    }


    function test_purge() {
        $this->assertFalse($this->cache->useCache(array('purge' => true)));
    }
}