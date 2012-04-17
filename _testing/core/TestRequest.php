<?php
/**
 * Simulates a full DokuWiki HTTP Request and allows
 * runtime inspection.
 */

/**
 * Helper class to execute a fake request
 */
class TestRequest {

    /**
     * Executes the request
     *
     * @return TestResponse response
     */
    function execute() {
        global $output_buffer;
        $output_buffer = '';

        // now execute dokuwiki and grep the output
        header_remove();
        ob_start('ob_start_callback');
        include(DOKU_INC.'doku.php');
        ob_end_flush();

        // it's done, return the page result
        return new TestResponse(
                $output_buffer,
                headers_list()
            );
    }
}

/**
 * holds a copy of all produced outputs of a TestRequest
 */
class TestResponse {
    protected $content;
    protected $headers;
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
        if(is_null($pq)) $pq = phpQuery::newDocument($this->content);
        return pq($selector);
    }
}
