<?php

/**
 * XMLRPC API backend
 */

use dokuwiki\Remote\XmlRpcServer;

if (!defined('DOKU_INC')) define('DOKU_INC', __DIR__ . '/../../');

require_once(DOKU_INC . 'inc/init.php');
session_write_close();  //close session

$server = new XmlRpcServer(true);
try {
    $server->serve();
} catch (\Exception $e) {
    $server->error($e->getCode(), $e->getMessage());
}
