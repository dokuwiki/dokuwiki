<?php
/**
 * Class used to parse RSS and ATOM feeds
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */

/**
 * We override some methods of the original SimplePie class here
 */
class FeedParser extends SimplePie {

    /**
     * Constructor. Set some defaults
     */
    public function __construct(){
        parent::__construct();
        $this->enable_cache(false);
        $this->set_file_class('FeedParser_File');
    }

    /**
     * Backward compatibility for older plugins
     *
     * @param string $url
     */
    public function feed_url($url){
        $this->set_feed_url($url);
    }
}

/**
 * Fetch an URL using our own HTTPClient
 *
 * Replaces SimplePie's own class
 */
class FeedParser_File extends SimplePie_File {
    protected $http;
    /** @noinspection PhpMissingParentConstructorInspection */

    /**
     * Inititializes the HTTPClient
     *
     * We ignore all given parameters - they are set in DokuHTTPClient
     *
     * @inheritdoc
     */
    public function __construct($url, $timeout=10, $redirects=5,
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
    public function headers(){
        return $this->headers;
    }

    /** @inheritdoc */
    public function body(){
        return $this->body;
    }

    /** @inheritdoc */
    public function close(){
        return true;
    }

}
