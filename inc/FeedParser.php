<?php
/**
 * Class used to parse RSS and ATOM feeds
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) die('meh.');

/**
 * We override some methods of the original SimplePie class here
 */
class FeedParser extends SimplePie {

    /**
     * Constructor. Set some defaults
     */
    function __construct(){
        parent::__construct();
        $this->enable_cache(false);
        $this->set_file_class('FeedParser_File');
    }

    /**
     * Backward compatibility for older plugins
     */
    function feed_url($url){
        $this->set_feed_url($url);
    }
}

/**
 * Fetch an URL using our own HTTPClient
 *
 * Replaces SimplePie's own class
 */
class FeedParser_File extends SimplePie_File {
    var $http;
    var $useragent;
    var $success = true;
    var $headers = array();
    var $body;
    var $error;
    /** @noinspection PhpMissingParentConstructorInspection */

    /**
     * Inititializes the HTTPClient
     *
     * We ignore all given parameters - they are set in DokuHTTPClient
     *
     * @inheritdoc
     */
    function __construct($url, $timeout=10, $redirects=5,
                         $headers=null, $useragent=null, $force_fsockopen=false, $curl_options = array()) {
        $this->http    = new DokuHTTPClient();
        $this->success = $this->http->sendRequest($url);

        $this->headers = $this->http->resp_headers;
        $this->body    = $this->http->resp_body;
        $this->error   = $this->http->error;

        $this->method  = SIMPLEPIE_FILE_SOURCE_REMOTE | SIMPLEPIE_FILE_SOURCE_FSOCKOPEN;

        return $this->success;
    }

    /** @inheritdoc */
    function headers(){
        return $this->headers;
    }

    /** @inheritdoc */
    function body(){
        return $this->body;
    }

    /** @inheritdoc */
    function close(){
        return true;
    }

}
