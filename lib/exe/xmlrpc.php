<?php

/**
 * XMLRPC API backend
 */

use easywiki\Remote\XmlRpcServer;

if (!defined('WIKI_INC')) define('WIKI_INC', __DIR__ . '/../../');

require_once(WIKI_INC . 'inc/init.php');
session_write_close();  //close session

$server = new XmlRpcServer(true);
try {
    $server->serve();
} catch (\Exception $e) {
    $server->error($e->getCode() ?: 1, $e->getMessage());
}
