<?php
if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/../../');

// fix when '< ?xml' isn't on the very first line
if(isset($HTTP_RAW_POST_DATA)) $HTTP_RAW_POST_DATA = trim($HTTP_RAW_POST_DATA);

require_once(DOKU_INC.'inc/init.php');
require_once(DOKU_INC.'inc/remote.php');
session_write_close();  //close session

if(!$conf['xmlrpc']) die('XML-RPC server not enabled.');

/**
 * Contains needed wrapper functions and registers all available
 * XMLRPC functions.
 */
class dokuwiki_xmlrpc_server extends IXR_Server {
    var $remote;

    /**
     * Constructor. Register methods and run Server
     */
    function dokuwiki_xmlrpc_server(){
        $this->remote = new RemoteAPI();
        $this->remote->setDateTransformation(array($this, 'toDate'));
        $this->remote->setFileTransformation(array($this, 'toFile'));
        $this->IXR_Server();
    }

    function call($methodname, $args){
        try {
            $result = $this->remote->call($methodname, $args);
            return $result;
        } catch (RemoteAccessDeniedException $e) {
            if (!isset($_SERVER['REMOTE_USER'])) {
                header('HTTP/1.1 401 Unauthorized');
            } else {
                header('HTTP/1.1 403 Forbidden');
            }
            return new IXR_Error(-32603, "server error. not authorized to call method $methodname");
        } catch (RemoteException $e) {
            return new IXR_Error($e->getCode(), $e->getMessage());
        }
    }

    function toDate($data) {
        return new IXR_Date($data);
    }

    function toFile($data) {
        return new IXR_Base64($data);
    }
}

$server = new dokuwiki_xmlrpc_server();

// vim:ts=4:sw=4:et:
