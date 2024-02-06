<?php

namespace dokuwiki\Remote;

use IXR\DataType\Base64;
use IXR\DataType\Date;
use IXR\Exception\ServerException;
use IXR\Message\Error;
use IXR\Server\Server;

/**
 * Contains needed wrapper functions and registers all available XMLRPC functions.
 */
class XmlRpcServer extends Server
{
    protected $remote;

    /**
     * Constructor. Register methods and run Server
     */
    public function __construct($wait = false)
    {
        $this->remote = new Api();
        parent::__construct(false, false, $wait);
    }

    /** @inheritdoc */
    public function serve($data = false)
    {
        global $conf;
        if (!$conf['remote']) {
            throw new ServerException("XML-RPC server not enabled.", -32605);
        }
        if (!empty($conf['remotecors'])) {
            header('Access-Control-Allow-Origin: ' . $conf['remotecors']);
        }
        if (
            !isset($_SERVER['CONTENT_TYPE']) ||
            (
                strtolower($_SERVER['CONTENT_TYPE']) !== 'text/xml' &&
                strtolower($_SERVER['CONTENT_TYPE']) !== 'application/xml'
            )
        ) {
            throw new ServerException('XML-RPC server accepts XML requests only.', -32606);
        }

        parent::serve($data);
    }

    /**
     * @inheritdoc
     */
    protected function call($methodname, $args)
    {
        try {
            $result = $this->remote->call($methodname, $args);
            return $result;
        } catch (AccessDeniedException $e) {
            if (!isset($_SERVER['REMOTE_USER'])) {
                http_status(401);
                return new Error(-32603, "server error. not authorized to call method $methodname");
            } else {
                http_status(403);
                return new Error(-32604, "server error. forbidden to call the method $methodname");
            }
        } catch (RemoteException $e) {
            return new Error($e->getCode(), $e->getMessage());
        }
    }
}
