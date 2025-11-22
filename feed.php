<?php

/**
 * XML feed export
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 *
 * @global array $conf
 * @global Input $INPUT
 */

use easywiki\Feed\FeedCreator;
use easywiki\Feed\FeedCreatorOptions;
use easywiki\Cache\Cache;
use easywiki\ChangeLog\MediaChangeLog;
use easywiki\ChangeLog\PageChangeLog;
use easywiki\Extension\AuthPlugin;
use easywiki\Extension\Event;

if (!defined('WIKI_INC')) define('WIKI_INC', __DIR__ . '/');
require_once(WIKI_INC . 'inc/init.php');

//close session
session_write_close();

//feed disabled?
if (!actionOK('rss')) {
    http_status(404);
    echo '<error>RSS feed is disabled.</error>';
    exit;
}

$options = new FeedCreatorOptions();

// the feed is dynamic - we need a cache for each combo
// (but most people just use the default feed so it's still effective)
$key = implode('$', [
    $options->getCacheKey(),
    $INPUT->server->str('REMOTE_USER'),
    $INPUT->server->str('HTTP_HOST'),
    $INPUT->server->str('SERVER_PORT')
]);
$cache = new Cache($key, '.feed');

// prepare cache depends
$depends['files'] = getConfigFiles('main');
$depends['age'] = $conf['rss_update'];
$depends['purge'] = $INPUT->bool('purge');

// check cacheage and deliver if nothing has changed since last
// time or the update interval has not passed, also handles conditional requests
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Type: ' . $options->getMimeType());
header('X-Robots-Tag: noindex');
if ($cache->useCache($depends)) {
    http_conditionalRequest($cache->getTime());
    if ($conf['allowdebug']) header("X-CacheUsed: $cache->cache");
    echo $cache->retrieveCache();
    exit;
} else {
    http_conditionalRequest(time());
}

// create new feed
try {
    $feed = (new FeedCreator($options))->build();
    $cache->storeCache($feed);
    echo $feed;
} catch (Exception $e) {
    http_status(500);
    echo '<error>' . hsc($e->getMessage()) . '</error>';
    exit;
}
