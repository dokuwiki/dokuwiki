<?php

use dokuwiki\Manifest;

if (!defined('DOKU_INC')) {
    define('DOKU_INC', __DIR__ . '/../../');
}
if (!defined('NOSESSION')) define('NOSESSION', true); // no session or auth required here
require_once(DOKU_INC . 'inc/init.php');

if (!actionOK('manifest')) {
    http_status(404, 'Manifest has been disabled in DokuWiki configuration.');
    exit();
}

$manifest = new Manifest();
$manifest->sendManifest();
