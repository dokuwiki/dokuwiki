<?php

if (!defined('DOKU_INC')) {
    define('DOKU_INC', __DIR__ . '/../../');
}
require_once(DOKU_INC . 'inc/init.php');

if (!actionOK('manifest')) {
    http_status(404, 'Manifest has been disabled in DokuWiki configuration.');
    exit();
}

$manifest = new \dokuwiki\Manifest();
$manifest->sendManifest();
