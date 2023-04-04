<?php

namespace dokuwiki\Remote;

use IXR\DataType\Base64;
use IXR\DataType\Date;
use IXR\Exception\ServerException;
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
    public function __construct($wait=false)
    {
        $this->remote = new Api();
        $this->remote->setDateTransformation(array($this, 'toDate'));
        $this->remote->setFileTransformation(array($this, 'toFile'));
        parent::__construct(false, false, $wait);
    }

    /** @inheritdoc  */
    public function serve($data = false)
    {
        global $conf;
        if (!$conf['remote']) {
            throw new ServerException("XML-RPC server not enabled.", -32605);
        }
        if (!empty($conf['remotecors'])) {
            header('Access-Control-Allow-Origin: ' . $conf['remotecors']);
        }

        parent::serve($data);
    }

    /**
     * @inheritdoc
     */
    public function call($methodname, $args)
    {
        try {
            $result = $this->remote->call($methodname, $args);
            return $result;
        } catch (AccessDeniedException $e) {
            if (!isset($_SERVER['REMOTE_USER'])) {
                http_status(401);
                return new ServerException("server error. not authorized to call method $methodname", -32603);
            } else {
                http_status(403);
                return new ServerException("server error. forbidden to call the method $methodname", -32604);
            }
        } catch (RemoteException $e) {
            return new ServerException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param string|int $data iso date(yyyy[-]mm[-]dd[ hh:mm[:ss]]) or timestamp
     * @return Date
     */
    public function toDate($data)
    {
        return new Date($data);
    }

    /**
     * @param string $data
     * @return Base64
     */
    public function toFile($data)
    {
        return new Base64($data);
    }
}
