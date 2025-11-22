<?php

use easywiki\Manifest;

if (!defined('WIKI_INC')) {
    define('WIKI_INC', __DIR__ . '/../../');
}
if (!defined('NOSESSION')) define('NOSESSION', true); // no session or auth required here
require_once(WIKI_INC . 'inc/init.php');

if (!actionOK('manifest')) {
    http_status(404, 'Manifest has been disabled in EasyWiki configuration.');
    exit();
}

$manifest = new Manifest();
$manifest->sendManifest();
