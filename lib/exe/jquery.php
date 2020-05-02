<?php

use dokuwiki\Cache\Cache;

if(!defined('DOKU_INC')) define('DOKU_INC', dirname(__FILE__) . '/../../');
if(!defined('NOSESSION')) define('NOSESSION', true); // we do not use a session or authentication here (better caching)
if(!defined('NL')) define('NL', "\n");
if(!defined('DOKU_DISABLE_GZIP_OUTPUT')) define('DOKU_DISABLE_GZIP_OUTPUT', 1); // we gzip ourself here
require_once(DOKU_INC . 'inc/init.php');

// MAIN
header('Content-Type: application/javascript; charset=utf-8');
jquery_out();

/**
 * Delivers the jQuery JavaScript
 *
 * We do absolutely nothing fancy here but concatenating the different files
 * and handling conditional and gzipped requests
 *
 * uses cache or fills it
 */
function jquery_out() {
    $cache = new Cache('jquery', '.js');
    $files = array(
        DOKU_INC . 'lib/scripts/jquery/jquery.min.js',
        DOKU_INC . 'lib/scripts/jquery/jquery-ui.min.js',
    );
    $cache_files = $files;
    $cache_files[] = __FILE__;

    // check cache age & handle conditional request
    // This may exit if a cache can be used
    $cache_ok = $cache->useCache(array('files' => $cache_files));
    http_cached($cache->cache, $cache_ok);

    $js = '';
    foreach($files as $file) {
        $js .= file_get_contents($file)."\n";
    }
    stripsourcemaps($js);

    http_cached_finish($cache->cache, $js);
}
