<?php

if (!defined('DOKU_INC')) {
    define('DOKU_INC', __DIR__ . '/../../');
}
require_once(DOKU_INC . 'inc/init.php');

$manifest = new \dokuwiki\Manifest();
$manifest->sendManifest();
