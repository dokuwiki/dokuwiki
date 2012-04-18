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
