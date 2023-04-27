<?php

namespace dokuwiki\Remote;

/**
 * Provide the Remote XMLRPC API as a JSON based API
 */
class JsonRpcServer
{

    protected $remote;

    /**
     * JsonRpcServer constructor.
     */
    public function __construct()
    {
        $this->remote = new Api();
        $this->remote->setFileTransformation(array($this, 'toFile'));
    }

    /**
     * Serve the request
     *
     * @return mixed
     * @throws RemoteException
     */
    public function serve()
    {
        global $conf;
        if (!$conf['remote']) {
            http_status(404);
            throw new RemoteException("JSON-RPC server not enabled.", -32605);
        }
        if (!empty($conf['remotecors'])) {
            header('Access-Control-Allow-Origin: ' . $conf['remotecors']);
        }

        global $INPUT;

        $call = $INPUT->server->str('PATH_INFO');
        $call = trim($call, '/');
        $args = json_decode(file_get_contents('php://input'), true);
        if (!is_array($args)) $args = [];

        return $this->call($call, $args);
    }

    /**
     * Call an API method
     *
     * @param string $methodname
     * @param array $args
     * @return mixed
     * @throws RemoteException
     */
    public function call($methodname, $args)
    {
        try {
            $result = $this->remote->call($methodname, $args);
            return $result;
        } catch (AccessDeniedException $e) {
            if (!isset($_SERVER['REMOTE_USER'])) {
                http_status(401);
                throw new RemoteException("server error. not authorized to call method $methodname", -32603);
            } else {
                http_status(403);
                throw new RemoteException("server error. forbidden to call the method $methodname", -32604);
            }
        } catch (RemoteException $e) {
            http_status(400);
            throw $e;
        }
    }

    /**
     * @param string $data
     * @return string
     */
    public function toFile($data)
    {
        return base64_encode($data);
    }

}
