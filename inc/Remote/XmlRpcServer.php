<?php

namespace dokuwiki\Remote;

/**
 * Contains needed wrapper functions and registers all available XMLRPC functions.
 */
class XmlRpcServer extends \IXR_Server
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

    /**
     * @inheritdoc
     */
    public function call($methodname, $args)
    {
        try {
            $result = $this->remote->call($methodname, $args);
            return $result;
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (AccessDeniedException $e) {
            if (!isset($_SERVER['REMOTE_USER'])) {
                http_status(401);
                return new \IXR_Error(-32603, "server error. not authorized to call method $methodname");
            } else {
                http_status(403);
                return new \IXR_Error(-32604, "server error. forbidden to call the method $methodname");
            }
        } catch (RemoteException $e) {
            return new \IXR_Error($e->getCode(), $e->getMessage());
        }
    }

    /**
     * @param string|int $data iso date(yyyy[-]mm[-]dd[ hh:mm[:ss]]) or timestamp
     * @return \IXR_Date
     */
    public function toDate($data)
    {
        return new \IXR_Date($data);
    }

    /**
     * @param string $data
     * @return \IXR_Base64
     */
    public function toFile($data)
    {
        return new \IXR_Base64($data);
    }
}
