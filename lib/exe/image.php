<?php

use dokuwiki\File\StaticImage;

if (!defined('DOKU_INC')) define('DOKU_INC', __DIR__ . '/../../');
if (!defined('NOSESSION')) define('NOSESSION', true);

require_once(DOKU_INC . 'inc/init.php');

global $INPUT;

$path = $INPUT->server->str('PATH_INFO');
$image = new StaticImage($path);
try {
    $image->serve();
} catch (\RuntimeException $e) {
    http_status(404);
    echo $e->getMessage();
}
