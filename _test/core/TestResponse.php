<?php

/**
 * holds a copy of all produced outputs of a TestRequest
 */
class TestResponse {
    /** @var string */
    protected $content;

    /** @var array */
    protected $headers;

    /** @var phpQueryObject */
    protected $pq = null;

    /** @var array */
    protected $data = array();

    /**
     * Constructor
     *
     * @param $content string the response body
     * @param $headers array the headers sent in the response
     * @param array $data any optional data passed back to the test system
     */
    function __construct($content, $headers, $data = array()) {
        $this->content = $content;
        $this->headers = $headers;
        $this->data = $data;
    }

    /**
     * Returns the response body
     *
     * @return string
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * Returns the headers set in the response
     *
     * @return array
     */
    public function getHeaders() {
        return $this->headers;
    }

    /**
     * Return a single header
     *
     * @param  $name   string, the name of the header without the ':', e.g. 'Content-Type', 'Pragma'
     * @return mixed   if exactly one header, the header (string); otherwise an array of headers, empty when no headers
     */
    public function getHeader($name) {
        $result = array();
        foreach($this->headers as $header) {
            if(substr($header, 0, strlen($name) + 1) == $name . ':') {
                $result[] = $header;
            }
        }

        return count($result) == 1 ? $result[0] : $result;
    }

    /**
     * Access the http status code
     *
     * in the test environment, only status codes explicitly set by dokuwiki are likely to be returned
     * this means succcessful status codes (e.g. 200 OK) will not be present, but error codes will be
     *
     * @return  int  http status code
     */
    public function getStatusCode() {
        $headers = $this->getHeader('Status');
        $code = null;

        if($headers) {
            // if there is more than one status header, use the last one
            $status = is_array($headers) ? array_pop($headers) : $headers;
            $matches = array();
            preg_match('/^Status: ?(\d+)/', $status, $matches);
            if($matches) {
                $code = $matches[1];
            }
        }

        return $code;
    }

    /**
     * Query the response for a JQuery compatible CSS selector
     *
     * @link https://code.google.com/p/phpquery/wiki/Selectors
     * @param $selector string
     * @return phpQueryObject
     */
    public function queryHTML($selector) {
        if(is_null($this->pq)) $this->pq = phpQuery::newDocument($this->content);
        return $this->pq->find($selector);
    }

    /**
     * Returns all collected data for the given key
     *
     * @param string $key
     * @return array
     */
    public function getData($key) {
        if(!isset($this->data[$key])) return array();
        return $this->data[$key];
    }
}
