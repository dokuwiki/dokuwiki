<?php
/**
 * Class HTTPMockClient
 *
 * Does not really mock the client, it still does real connections but will retry failed connections
 * to work around shaky connectivity.
 */
class HTTPMockClient extends HTTPClient {
    protected $tries;

    /**
     * Sets shorter timeout
     */
    function __construct() {
        parent::__construct();
        $this->timeout = 8; // slightly faster timeouts
    }

    /**
     * Returns true if the connection timed out
     *
     * @return bool
     */
    function noconnection() {
        return ($this->tries === 0);
    }

    /**
     * Retries sending the request multiple times
     *
     * @param string $url
     * @param string $data
     * @param string $method
     * @return bool
     */
    function sendRequest($url, $data = '', $method = 'GET') {
        $this->tries = 2; // configures the number of retries
        $return      = false;
        while($this->tries) {
            $return = parent::sendRequest($url, $data, $method);
            if($this->status != -100) break;
            $this->tries--;
        }
        return $return;
    }
}