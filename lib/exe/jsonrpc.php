<?php
/**
 * Created by PhpStorm.
 * User: z97
 * Date: 15-5-29
 * Time: 下午7:59
 */
if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/../../');
require_once(DOKU_INC.'inc/init.php');
session_write_close();  //close session
require_once(DOKU_INC."inc/jsonrpc_core.php");

function hello(){
    return 'hello how are you';
}

$func_list=array("hello"=>"hello");
$myapi =new \xx_jsonrpc\remote_api($func_list);

$myserver= new jsonrpc_server();
$myserver->server();