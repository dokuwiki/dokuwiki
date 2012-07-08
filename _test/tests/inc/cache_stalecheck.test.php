<?php

class cache_stalecheck_test extends DokuWikiTest {
	function test_staleness() {
		global $ID;

		$ID = 'stale';
		$file = wikiFN($ID);

		# Prepare test page
		saveWikiText($ID, 'Fresh', 'Created');

		# Create stale cache
		$cache = new cache_renderer($ID, $file, 'xhtml');
		$cache->storeCache('Stale');
		$stale = $cache->retrieveCache();

		# Prepare stale cache for testing
		$time = filemtime($file);
		touch($cache->cache, $time);

		# Make the test
		$fresh = p_cached_output($file, 'xhtml', $ID);
		$this->assertNotEquals($fresh, $stale, 'Stale cache failed to expire');
	}
}

