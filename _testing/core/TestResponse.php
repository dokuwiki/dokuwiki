<?php
/**
 * holds a copy of all produced outputs of a TestRequest
 */
class TestResponse {
    protected $content;
    protected $headers;

    /**
     * @var phpQueryObject
     */
    protected $pq = null;

    function __construct($content, $headers) {
        $this->content = $content;
        $this->headers = $headers;
    }

    function getContent() {
        return $this->content;
    }

    function getHeaders() {
        return $this->headers;
    }

    /**
     * Query the response for a JQuery compatible CSS selector
     *
     * @link    https://code.google.com/p/phpquery/wiki/Selectors
     * @param   string selector
     * @returns object a PHPQuery object
     */
    function queryHTML($selector){
        if(is_null($this->pq)) $this->pq = phpQuery::newDocument($this->content);
        return $this->pq->find($selector);
    }
}
