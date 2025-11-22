<?php

use easywiki\Remote\JsonRpcServer;

if (!defined('WIKI_INC')) define('WIKI_INC', __DIR__ . '/../../');

require_once(WIKI_INC . 'inc/init.php');
session_write_close();  //close session

header('Content-Type: application/json');

$server = new JsonRpcServer();
try {
    $result = $server->serve();
} catch (\Exception $e) {
    $result = $server->returnError($e);
}

echo json_encode($result, JSON_THROW_ON_ERROR);
