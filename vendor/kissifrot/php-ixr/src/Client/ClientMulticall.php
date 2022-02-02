<?php
namespace IXR\Client;

/**
 * IXR_ClientMulticall
 *
 * @package IXR
 * @since 1.5.0
 */
class ClientMulticall extends Client
{
    private $calls = [];

    public function __construct($server, $path = false, $port = 80)
    {
        parent::__construct($server, $path, $port);
        $this->useragent = 'The Incutio XML-RPC PHP Library (multicall client)';
    }

    public function addCall()
    {
        $args = func_get_args();
        $methodName = array_shift($args);
        $struct = [
            'methodName' => $methodName,
            'params' => $args
        ];
        $this->calls[] = $struct;
    }

    public function query()
    {
        // Prepare multicall, then call the parent::query() method
        return parent::query('system.multicall', $this->calls);
    }
}
