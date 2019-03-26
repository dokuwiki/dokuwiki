<?php

use dokuwiki\HTTP\HTTPClient;

/**
 * Class HTTPMockClient
 *
 * Does not really mock the client, it still does real connections but will retry failed connections
 * to work around shaky connectivity.
 */
class HTTPMockClient extends HTTPClient {
    protected $tries;
    protected $lasturl;

    /**
     * Sets shorter timeout
     */
    public function __construct() {
        parent::__construct();
        $this->timeout = 8; // slightly faster timeouts
    }

    /**
     * Returns true if the connection timed out
     *
     * @return bool
     */
    public function noconnection() {
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
    public function sendRequest($url, $data = '', $method = 'GET') {
        $this->lasturl = $url;
        $this->tries = 2; // configures the number of retries
        $return      = false;
        while($this->tries) {
            $return = parent::sendRequest($url, $data, $method);
            if($this->status != -100 && $this->status != 408) break;
            usleep((3 - $this->tries) * 250000);
            $this->tries--;
        }
        return $return;
    }

    /**
     * Return detailed error data
     *
     * @param string $info optional additional info
     * @return string
     */
    public function errorInfo($info = '') {
        return json_encode(
            array(
                'URL' => $this->lasturl,
                'Error' => $this->error,
                'Status' => $this->status,
                'Body' => $this->resp_body,
                'Info' => $info
            ), JSON_PRETTY_PRINT
        );
    }
}
