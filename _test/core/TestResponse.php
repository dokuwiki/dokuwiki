<?php
/**
 * holds a copy of all produced outputs of a TestRequest
 */
class TestResponse {
    /**
     * @var string
     */
    private $content;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var phpQueryObject
     */
    private $pq = null;

    /**
     * @param $content string
     * @param $headers array
     */
    function __construct($content, $headers) {
        $this->content = $content;
        $this->headers = $headers;
    }

    /**
     * @return string
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * @return array
     */
    public function getHeaders() {
        return $this->headers;
    }

    /**
     * @param  $name   string, the name of the header without the ':', e.g. 'Content-Type', 'Pragma'
     * @return mixed   if exactly one header, the header (string); otherwise an array of headers, empty when no headers
     */
    public function getHeader($name) {
        $result = array();
        foreach ($this->headers as $header) {
            if (substr($header,0,strlen($name)+1) == $name.':') {
                $result[] = $header;
            }
        }

        return count($result) == 1 ? $result[0] : $result;
    }

    /**
     * @return  int  http status code
     *
     * in the test environment, only status codes explicitly set by dokuwiki are likely to be returned
     * this means succcessful status codes (e.g. 200 OK) will not be present, but error codes will be
     */
    public function getStatusCode() {
        $headers = $this->getHeader('Status');
        $code = null;

        if ($headers) {
            // if there is more than one status header, use the last one
            $status = is_array($headers) ? array_pop($headers) : $headers;
            $matches = array();
            preg_match('/^Status: ?(\d+)/',$status,$matches);
            if ($matches){
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
    public function queryHTML($selector){
        if(is_null($this->pq)) $this->pq = phpQuery::newDocument($this->content);
        return $this->pq->find($selector);
    }
}
