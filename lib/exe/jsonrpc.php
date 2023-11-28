<?php

use dokuwiki\Remote\JsonRpcServer;

if (!defined('DOKU_INC')) define('DOKU_INC', __DIR__ . '/../../');

require_once(DOKU_INC . 'inc/init.php');
session_write_close();  //close session

header('Content-Type: application/json');

$server = new JsonRpcServer();
try {
    $result = [
        'error' => [
            'code' => 0,
            'message' => 'success'
        ],
        'data' => $server->serve(),
    ];
} catch (\Exception $e) {
    $result = [
        'error' => [
            'code' => $e->getCode(),
            'message' => $e->getMessage()
        ],
        'data' => null,
    ];
}

echo json_encode($result);
