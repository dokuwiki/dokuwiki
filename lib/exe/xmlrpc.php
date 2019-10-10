<?php
/**
 * XMLRPC API backend
 */

use dokuwiki\Remote\XmlRpcServer;

if(!defined('DOKU_INC')) define('DOKU_INC', dirname(__FILE__).'/../../');

require_once(DOKU_INC.'inc/init.php');
session_write_close();  //close session

if(!$conf['remote']) die((new IXR_Error(-32605, "XML-RPC server not enabled."))->getXml());

$server = new XmlRpcServer();
