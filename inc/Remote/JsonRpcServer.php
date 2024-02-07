<?php

namespace dokuwiki\Remote;

/**
 * Provide the Remote XMLRPC API as a JSON based API
 */
class JsonRpcServer
{
    protected $remote;

    /** @var float The XML-RPC Version. 0 is our own simplified variant */
    protected $version = 0;

    /**
     * JsonRpcServer constructor.
     */
    public function __construct()
    {
        $this->remote = new Api();
    }

    /**
     * Serve the request
     *
     * @param string $body Should only be set for testing, otherwise the request body is read from php://input
     * @return mixed
     * @throws RemoteException
     */
    public function serve($body = '')
    {
        global $conf;
        global $INPUT;

        if (!$conf['remote']) {
            http_status(404);
            throw new RemoteException("JSON-RPC server not enabled.", -32605);
        }
        if (!empty($conf['remotecors'])) {
            header('Access-Control-Allow-Origin: ' . $conf['remotecors']);
        }
        if ($INPUT->server->str('REQUEST_METHOD') !== 'POST') {
            http_status(405);
            header('Allow: POST');
            throw new RemoteException("JSON-RPC server only accepts POST requests.", -32606);
        }
        if ($INPUT->server->str('CONTENT_TYPE') !== 'application/json') {
            http_status(415);
            throw new RemoteException("JSON-RPC server only accepts application/json requests.", -32606);
        }

        try {
            if ($body === '') {
                $body = file_get_contents('php://input');
            }
            if ($body !== '') {
                $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
            } else {
                $data = [];
            }
        } catch (\Exception $e) {
            http_status(400);
            throw new RemoteException("JSON-RPC server only accepts valid JSON.", -32700);
        }

        return $this->createResponse($data);
    }

    /**
     * This executes the method and returns the result
     *
     * This should handle all JSON-RPC versions and our simplified version
     *
     * @link https://en.wikipedia.org/wiki/JSON-RPC
     * @link https://www.jsonrpc.org/specification
     * @param array $data
     * @return array
     * @throws RemoteException
     */
    protected function createResponse($data)
    {
        global $INPUT;
        $return = [];

        if (isset($data['method'])) {
            // this is a standard conform request (at least version 1.0)
            $method = $data['method'];
            $params = $data['params'] ?? [];
            $this->version = 1;

            // always return the same ID
            if (isset($data['id'])) $return['id'] = $data['id'];

            // version 2.0 request
            if (isset($data['jsonrpc'])) {
                $return['jsonrpc'] = $data['jsonrpc'];
                $this->version = (float)$data['jsonrpc'];
            }

            // version 1.1 request
            if (isset($data['version'])) {
                $return['version'] = $data['version'];
                $this->version = (float)$data['version'];
            }
        } else {
            // this is a simplified request
            $method = $INPUT->server->str('PATH_INFO');
            $method = trim($method, '/');
            $params = $data;
            $this->version = 0;
        }

        // excute the method
        $return['result'] = $this->call($method, $params);
        $this->addErrorData($return); // handles non-error info
        return $return;
    }

    /**
     * Create an error response
     *
     * @param \Exception $exception
     * @return array
     */
    public function returnError($exception)
    {
        $return = [];
        $this->addErrorData($return, $exception);
        return $return;
    }

    /**
     * Depending on the requested version, add error data to the response
     *
     * @param array $response
     * @param \Exception|null $e
     * @return void
     */
    protected function addErrorData(&$response, $e = null)
    {
        if ($e !== null) {
            // error occured, add to response
            $response['error'] = [
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        } else {
            // no error, act according to version
            if ($this->version > 0 && $this->version < 2) {
                // version 1.* wants null
                $response['error'] = null;
            } elseif ($this->version < 1) {
                // simplified version wants success
                $response['error'] = [
                    'code' => 0,
                    'message' => 'success'
                ];
            }
            // version 2 wants no error at all
        }
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
            return $this->remote->call($methodname, $args);
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
}
