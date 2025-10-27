<?php
namespace IXR\Client;

use IXR\Message\Error;
use IXR\Message\Message;
use IXR\Request\Request;

/**
 * IXR_Client
 *
 * @package IXR
 * @since   1.5.0
 *
 */
class Client
{
    protected $server;
    protected $port;
    protected $path;
    protected $useragent;
    protected $response;
    /** @var bool|Message */
    protected $message = false;
    protected $debug = false;
    /** @var int Connection timeout in seconds */
    protected $timeout;
    /** @var null|int Timeout for actual data transfer; in seconds */
    protected $timeout_io = null;
    protected $headers = [];

    /**
     * @var null|Error
     *
     * Storage place for an error message
     */
    private $error = null;

    public function __construct($server, $path = false, $port = 80, $timeout = 15, $timeout_io = null)
    {
        if (!$path) {
            // Assume we have been given a URL instead
            $bits = parse_url($server);
            $this->server = $bits['host'];
            $this->port = isset($bits['port']) ? $bits['port'] : 80;
            $this->path = isset($bits['path']) ? $bits['path'] : '/';

            // Make absolutely sure we have a path
            if (!$this->path) {
                $this->path = '/';
            }

            if (!empty($bits['query'])) {
                $this->path .= '?' . $bits['query'];
            }
        } else {
            $this->server = $server;
            $this->path = $path;
            $this->port = $port;
        }
        $this->useragent = 'The Incutio XML-RPC PHP Library';
        $this->timeout = $timeout;
        $this->timeout_io = $timeout_io;
    }

    public function query()
    {
        $args = func_get_args();
        $method = array_shift($args);
        $request = new Request($method, $args);
        $length = $request->getLength();
        $xml = $request->getXml();
        $r = "\r\n";
        $request = "POST {$this->path} HTTP/1.0$r";

        // Merged from WP #8145 - allow custom headers
        $this->headers['Host'] = $this->server;
        $this->headers['Content-Type'] = 'text/xml';
        $this->headers['User-Agent'] = $this->useragent;
        $this->headers['Content-Length'] = $length;

        foreach ($this->headers as $header => $value) {
            $request .= "{$header}: {$value}{$r}";
        }
        $request .= $r;

        $request .= $xml;

        // Now send the request
        if ($this->debug) {
            echo '<pre class="ixr_request">' . htmlspecialchars($request) . "\n</pre>\n\n";
        }

        if ($this->timeout) {
            try {
                $fp = fsockopen($this->server, $this->port, $errno, $errstr, $this->timeout);
            } catch (\Exception $e) {
                $fp = false;
            }
        } else {
            try {
                $fp = fsockopen($this->server, $this->port, $errno, $errstr);
            } catch (\Exception $e) {
                $fp = false;
            }
        }
        if (!$fp) {
            return $this->handleError(-32300, 'transport error - could not open socket');
        }
        if (null !== $this->timeout_io) {
            stream_set_timeout($fp, $this->timeout_io);
        }
        fputs($fp, $request);
        $contents = '';
        $debugContents = '';
        $gotFirstLine = false;
        $gettingHeaders = true;
        while (!feof($fp)) {
            $line = fgets($fp, 4096);
            if (!$gotFirstLine) {
                // Check line for '200'
                if (strstr($line, '200') === false) {
                    return $this->handleError(-32300, 'transport error - HTTP status code was not 200');
                }
                $gotFirstLine = true;
            }
            if (trim($line) == '') {
                $gettingHeaders = false;
            }
            if (!$gettingHeaders) {
                // merged from WP #12559 - remove trim
                $contents .= $line;
            }
            if ($this->debug) {
                $debugContents .= $line;
            }
        }
        if ($this->debug) {
            echo '<pre class="ixr_response">' . htmlspecialchars($debugContents) . "\n</pre>\n\n";
        }

        // Now parse what we've got back
        $this->message = new Message($contents);
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

    public function getResponse()
    {
        // methodResponses can only have one param - return that
        return $this->message->params[0];
    }

    public function isError()
    {
        return (is_object($this->error));
    }

    protected function handleError($errorCode, $errorMessage)
    {
        $this->error = new Error($errorCode, $errorMessage);

        return false;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getErrorCode()
    {
        return $this->error->code;
    }

    public function getErrorMessage()
    {
        return $this->error->message;
    }


    /**
     * Gets the current timeout set for data transfer
     * @return int|null
     */
    public function getTimeoutIo()
    {
        return $this->timeout_io;
    }

    /**
     * Sets the timeout for data transfer
     * @param int $timeout_io
     */
    public function setTimeoutIo($timeout_io)
    {
        $this->timeout_io = $timeout_io;
    }
}
