<?php

namespace dokuwiki\Remote\IXR;

use dokuwiki\HTTP\HTTPClient;
use IXR\Message\Message;
use IXR\Request\Request;

/**
 * This implements a XML-RPC client using our own HTTPClient
 *
 * Note: this now inherits from the IXR library's client and no longer from HTTPClient. Instead composition
 * is used to add the HTTP client.
 */
class Client extends \IXR\Client\Client
{
    /** @var HTTPClient */
    protected $httpClient;

    /** @var string */
    protected $posturl = '';

    /** @inheritdoc */
    public function __construct($server, $path = false, $port = 80, $timeout = 15, $timeout_io = null)
    {
        parent::__construct($server, $path, $port, $timeout, $timeout_io);
        if (!$path) {
            // Assume we have been given an URL instead
            $this->posturl = $server;
        } else {
            $this->posturl = 'http://' . $server . ':' . $port . $path;
        }

        $this->httpClient = new HTTPClient();
        $this->httpClient->timeout = $timeout;
    }

    /** @inheritdoc */
    public function query(...$args)
    {
        $method = array_shift($args);
        $request = new Request($method, $args);
        $length = $request->getLength();
        $xml = $request->getXml();

        $this->headers['Content-Type'] = 'text/xml';
        $this->headers['Content-Length'] = $length;
        $this->httpClient->headers = array_merge($this->httpClient->headers, $this->headers);

        if (!$this->httpClient->sendRequest($this->posturl, $xml, 'POST')) {
            $this->handleError(-32300, 'transport error - ' . $this->httpClient->error);
            return false;
        }

        // Check HTTP Response code
        if ($this->httpClient->status < 200 || $this->httpClient->status > 206) {
            $this->handleError(-32300, 'transport error - HTTP status ' . $this->httpClient->status);
            return false;
        }

        // Now parse what we've got back
        $this->message = new Message($this->httpClient->resp_body);
        if (!$this->message->parse()) {
            // XML error
            return $this->handleError(-32700, 'Parse error. Message not well formed');
        }

        // Is the message a fault?
        if ($this->message->messageType == 'fault') {
            return $this->handleError($this->message->faultCode, $this->message->faultString);
        }

        // Message must be OK
        return true;
    }

    /**
     * Direct access to the underlying HTTP client if needed
     *
     * @return HTTPClient
     */
    public function getHTTPClient()
    {
        return $this->httpClient;
    }
}
